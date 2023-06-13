<?php

namespace Ds\Http\Controllers;

use Ds\Domain\Shared\DataTable;
use Ds\Domain\Shared\Exceptions\DisclosableException;
use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Jobs\GenerateTaxReceipts;
use Ds\Models\Member as Account;
use Ds\Models\Order;
use Ds\Models\TaxReceipt;
use Ds\Models\TaxReceiptTemplate;
use Ds\Models\Transaction;
use Ds\Repositories\AccountRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\LazyCollection;
use Illuminate\Support\Str;

class TaxReceiptsController extends Controller
{
    /**
     * View tax receipt list.
     */
    public function index()
    {
        // tax receipts
        user()->canOrRedirect('taxreceipt');

        // return view
        return view('tax_receipts.index', [
            '__menu' => 'reports.tax_receipts',
            'filters' => (object) [
                'search' => request('search'),
                'filter_by' => request('filter_by'),
                'issued_at_str' => request('issued_at_str'),
                'issued_at_end' => request('issued_at_end'),
            ],
        ]);
    }

    /**
     * Ajax data for receipt list.
     */
    public function index_ajax()
    {
        // tax receipts
        user()->canOrRedirect('taxreceipt');

        // generate data table
        $dataTable = new DataTable($this->_baseQueryWithFilters(), [
            'id',
            'number',
            'name',
            'email',
            'amount',
            'issued_at',
            'deleted_at',
            'transaction_id',
            'order_id',
            'status',
            'account_id',
            'first_name',
            'last_name',
        ]);

        // format results
        $dataTable->setFormatRowFunction(function ($receipt) {
            $number = '<a href="/jpanel/tax_receipt/' . e($receipt->id) . '/pdf" target="_blank" class="ds-tax-receipt" data-tax-receipt-id="' . e($receipt->id) . '">' . e($receipt->number) . '</a>';

            if ($receipt->status === 'draft') {
                $number .= '&nbsp;<span class="pull-right label label-xs label-default">DRAFT</span>';
            } elseif ($receipt->status === 'void') {
                $number .= '&nbsp;<span class="pull-right label label-xs label-danger">VOID</span>';
            }

            $account = trim($receipt->name) ?: '(blank)';

            if ($receipt->account_id) {
                $account = sprintf('<a href="%s" target="_blank">%s</a>', route('backend.member.edit', $receipt->account_id), e($account));
            }

            return [
                e($receipt->id),
                dangerouslyUseHTML($number),
                dangerouslyUseHTML($account),
                dangerouslyUseHTML($receipt->email ? ('<a href="mailto:' . e($receipt->email) . '">' . e($receipt->email) . '</a>') : ''),
                dangerouslyUseHTML('<div class="stat-val">' . e(number_format($receipt->amount, 2)) . '&nbsp;<span class="text-muted">' . e($receipt->currency_code) . '</span></div>'),
                dangerouslyUseHTML(e(toLocalFormat($receipt->issued_at)) . ' <small class="text-muted">' . e(toLocalFormat($receipt->getAttributeValue('issued_at'), 'humans')) . '</small>'),

                (bool) $receipt->status === 'void',
            ];
        });

        // return datatable JSON
        return response($dataTable->make());
    }

    /**
     * CSV output
     */
    public function index_csv()
    {
        // tax receipts
        user()->canOrRedirect('taxreceipt');

        // generate data table
        $receipts = $this->_baseQueryWithFilters()
            ->get();

        // output CSV
        header('Content-type: text/csv');
        header('Content-type: text/plain');
        header('Cache-Control: no-store, no-cache');
        header('Content-Disposition: attachment; filename="tax_receipts.csv"');
        $outstream = fopen('php://output', 'w');
        fputcsv($outstream, ['Number', 'Amount', 'Issued At', 'Name', 'Address', 'Address 2', 'City', 'State', 'ZIP', 'Phone', 'Email'], ',', '"');
        foreach ($receipts as $receipt) {
            fputcsv($outstream, [
                $receipt->number,
                number_format($receipt->amount, 2),
                toLocalFormat($receipt->issued_at, 'csv'),
                $receipt->name,
                $receipt->address_01,
                $receipt->address_02,
                $receipt->city,
                $receipt->state,
                $receipt->zip,
                $receipt->phone,
                $receipt->email,
            ], ',', '"');
        }
        fclose($outstream);
        exit;
    }

    /**
     * View record as PDF
     *
     * @param int $receiptId
     * @return \Ds\Common\Pdf
     */
    public function pdf($receiptId)
    {
        $receipt = TaxReceipt::findWithPermission($receiptId);

        return $receipt->toPDF();
    }

    /**
     * View modal
     *
     * @param int $receiptId
     * @return \Illuminate\View\View
     */
    public function modal($receiptId)
    {
        $receipt = TaxReceipt::findWithPermission($receiptId);

        // disable parent views
        $this->setViewLayout(false);

        return $this->getView('tax_receipts/modal', [
            'receipt' => $receipt,
        ]);
    }

    /**
     * Revise the tax receipt
     *
     * @param int $receiptId
     * @return \Illuminate\Http\JsonResponse
     */
    public function revise($receiptId)
    {
        $receipt = TaxReceipt::findWithPermission($receiptId, 'edit');

        $changes = [
            'name' => request('name'),
            'first_name' => request('first_name'),
            'last_name' => request('last_name'),
            'address_01' => request('address_01'),
            'address_02' => request('address_02'),
            'city' => request('city'),
            'state' => request('state'),
            'zip' => request('zip'),
            'country' => request('country'),
            'email' => request('email'),
            'phone' => request('phone'),
            'amount' => request('amount'),
            'issued_at' => (request('issued_at')) ? toUtc(request('issued_at'))->startOfDay() : null,
        ];

        if ($receipt->status === 'draft') {
            foreach ($changes as $attribute => $value) {
                if ($attribute === 'amount') {
                    $value = numeral($value)->toFloat() ?? 0;
                }

                $receipt->setAttribute($attribute, $value);
            }

            $receipt->save();
        } elseif ($receipt->status === 'issued') {
            $receipt->revise($changes);
        }

        return response()->json($receipt);
    }

    /**
     * Issue the tax receipt
     *
     * @param int $receiptId
     * @return \Illuminate\Http\JsonResponse
     */
    public function issue($receiptId)
    {
        $receipt = TaxReceipt::findWithPermission($receiptId, 'edit');

        if ($receipt->status === 'draft') {
            $receipt->status = 'issued';
            $receipt->issued_at = $receipt->issued_at ?? fromLocal('now');
            $receipt->save();
        }

        return response()->json($receipt);
    }

    /**
     * Void (delete) the tax receipt
     *
     * @param int $receiptId
     * @return \Illuminate\Http\JsonResponse
     */
    public function void($receiptId)
    {
        $receipt = TaxReceipt::findWithPermission($receiptId, 'edit');
        $receipt->void();

        return response()->json($receipt);
    }

    /**
     * Notify the receipient of the tax receipt.
     *
     * @param int $receiptId
     * @return \Illuminate\Http\JsonResponse
     */
    public function notify($receiptId)
    {
        $receipt = TaxReceipt::findWithPermission($receiptId);
        $receipt->notify();

        return response()->json($receipt);
    }

    /**
     * Build a base query based on request filter params.
     * Allows us to reuse this for datatables, csv, etc...
     */
    private function _baseQueryWithFilters()
    {
        $receipts = TaxReceipt::with('account');

        $filters = (object) [];

        // search
        $filters->search = request('search');
        if (Str::startsWith($filters->search, 'receipts:')) {
            $numbers = array_map('trim', explode(',', substr($filters->search, 9)));
            $receipts->whereIn('number', $numbers);
        } elseif ($filters->search) {
            $receipts->where(function ($query) use ($filters) {
                $query->where('name', 'like', "%$filters->search%");
                $query->orWhere('first_name', 'like', "%$filters->search%");
                $query->orWhere('last_name', 'like', "%$filters->search%");
                $query->orWhere('address_01', 'like', "%$filters->search%");
                $query->orWhere('address_02', 'like', "%$filters->search%");
                $query->orWhere('zip', 'like', "%$filters->search%");
                $query->orWhere('phone', 'like', "%$filters->search%");
                $query->orWhere('email', 'like', "%$filters->search%");
                $query->orWhere('amount', 'like', "%$filters->search%");
                $query->orWhere('number', 'like', "%$filters->search%");
            });
        }

        // filter by
        $filters->filter_by = request('filter_by');
        if ($filters->filter_by) {
            if ($filters->filter_by === 'no_name') {
                $receipts->where(function ($query) {
                    $query->whereNull('name');
                    $query->orWhere('name', '=', '');
                });
            } elseif ($filters->filter_by === 'no_email') {
                $receipts->whereNull('email');
            } elseif ($filters->filter_by === 'incomplete_address') {
                $receipts->where(function ($query) use ($filters) {
                    $query->whereNull('address_01');
                    $query->orWhereNull('city');
                    $query->orWhereNull('state');
                    $query->orWhereNull('zip');
                });
            }
        }

        // status
        $filters->status = request('status');
        if ($filters->status) {
            $receipts->where('status', $filters->status);
        }

        // issued date
        $filters->issued_at_str = fromLocal(request('issued_at_str'));
        $filters->issued_at_end = fromLocal(request('issued_at_end'));
        if ($filters->issued_at_str && $filters->issued_at_end) {
            $receipts->whereBetween('issued_at', [
                toUtc($filters->issued_at_str->startOfDay()),
                toUtc($filters->issued_at_end->endOfDay()),
            ]);
        } elseif ($filters->issued_at_str) {
            $receipts->where('issued_at', '>=', toUtc($filters->issued_at_str->startOfDay()));
        } elseif ($filters->issued_at_end) {
            $receipts->where('issued_at', '<=', toUtc($filters->issued_at_end->endOfDay()));
        }

        return $receipts;
    }

    /**
     * @return \Illuminate\View\View
     */
    public function newReceipt()
    {
        user()->canOrRedirect('taxreceipt.edit');

        $account = Account::findOrFail(request('account'));

        $receipt = new TaxReceipt;
        $receipt->receipt_type = request('type');
        $receipt->account_id = $account->id;

        $templates = TaxReceiptTemplate::query()
            ->where('template_type', 'template')
            ->orderBy('name')
            ->get();

        return view('tax_receipts.new', [
            'account' => $account,
            'receipt' => $receipt,
            'templates' => $templates,
        ]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function receiptable(AccountRepository $accountRepository)
    {
        user()->canOrRedirect('taxreceipt.edit');

        $account = Account::findOrFail(request('account'));
        $receiptingPeriod = request('receipting_period', 'last_year');

        if ($receiptingPeriod === 'this_year') {
            $receiptingPeriodStart = fromLocal('this year')->startOfYear();
            $receiptingPeriodEnd = fromLocal('this year')->endOfYear();
        } elseif ($receiptingPeriod === 'last_year') {
            $receiptingPeriodStart = fromLocal('last year')->startOfYear();
            $receiptingPeriodEnd = fromLocal('last year')->endOfYear();
        } else {
            $receiptingPeriodStart = null;
            $receiptingPeriodEnd = null;
        }

        $data = $accountRepository
            ->getReceiptableAmounts(
                $account,
                request('min_receiptable', 0),
                $receiptingPeriodStart,
                $receiptingPeriodEnd
            )->map(function ($row) {
                if (Str::startsWith($row->description, 'Contribution #')) {
                    $description = preg_replace('/^Contribution #/', '', $row->description);
                    $description = sprintf(
                        'Contribution <a href="%s">#%s</a>',
                        route('backend.orders.edit_without_id', ['c' => $description]),
                        $description
                    );
                } elseif (Str::startsWith($row->description, 'Recurring Payment #')) {
                    $description = preg_replace(
                        '/^(Recurring Payment )#(.*?)(-.*)$/',
                        '$1<a href="/jpanel/recurring_payments/$2">#$2$3</a>',
                        $row->description
                    );
                } else {
                    $description = $row->description;
                }

                return [
                    '<input type="checkbox" class="slave" name="selectedids[]" value="' . $row->identifier . '" />',
                    $description,
                    toLocalFormat($row->date) . ' <small class="text-muted">' . toLocalFormat($row->date, 'g:iA') . '</small>',
                    '<div class="stat-val">' . numeral($row->receiptable_amount) . '&nbsp;<span class="text-muted">' . currency($row->currency_code) . '</span></div>',
                    ucwords($row->receipt_type),
                ];
            });

        return response([
            'draw' => (int) request('draw', 1),
            'recordsTotal' => count($data),
            'recordsFiltered' => count($data),
            'data' => $data,
        ]);
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function createReceipt()
    {
        user()->canOrRedirect('taxreceipt.edit');

        $validator = app('validator')->make(request()->all(), [
            'account_id' => 'required|exists:Ds\Models\Member,id',
            'status' => 'required|in:issued,draft',
            'receipt_type' => 'required|in:consolidated,single',
            'selectedids' => 'required|array',
            'tax_receipt_template_id' => 'required|exists:Ds\Models\TaxReceiptTemplate,id',
        ]);

        if ($validator->fails()) {
            $this->flash->error($validator->errors()->first());

            return redirect()->back();
        }

        $orders = collect();
        $transactions = collect();

        try {
            foreach (request('selectedids') as $id) {
                if (Str::startsWith($id, 'order_')) {
                    $id = Str::after($id, 'order_');
                    $orders[] = Order::findOrFail($id);
                } elseif (Str::startsWith($id, 'transaction_')) {
                    $id = Str::after($id, 'transaction_');
                    $transactions[] = Transaction::findOrFail($id);
                }
            }
        } catch (ModelNotFoundException $e) {
            $this->flash->error('Invalid Contribution/Transaction');

            return redirect()->back();
        }

        $account = Account::find(request('account_id'));
        $taxReceiptTemplate = TaxReceiptTemplate::find(request('tax_receipt_template_id'));

        $receipt = new TaxReceipt;
        $receipt->status = request('status');
        $receipt->receipt_type = request('receipt_type');
        $receipt->issued_at = fromLocal(request('receipt_date', 'now'));
        $receipt->currency_code = (string) currency();
        $receipt->setAccount($account);
        $receipt->setTemplate($taxReceiptTemplate);
        $receipt->save();

        foreach ($orders as $order) {
            $receipt->attachOrder($order);
        }

        foreach ($transactions as $transaction) {
            $receipt->attachTransaction($transaction);
        }

        if (request('auto_notify')) {
            try {
                $receipt->notify();
            } catch (MessageException $e) {
                $this->flash->error($e->getMessage());
            }
        }

        return redirect('jpanel/tax_receipts');
    }

    /**
     * @return \Illuminate\View\View
     */
    public function consolidated()
    {
        user()->canOrRedirect('taxreceipt.edit');

        $templates = TaxReceiptTemplate::query()
            ->where('template_type', 'template')
            ->orderBy('name')
            ->get();

        return view('tax_receipts.consolidated', [
            'templates' => $templates,
        ]);
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function batchCreateReceipts()
    {
        user()->canOrRedirect('taxreceipt.edit');

        try {
            dispatch(new GenerateTaxReceipts([
                'receipting_period_from' => fromLocal(request('receipting_period_from'))->startOfDay(),
                'receipting_period_to' => fromLocal(request('receipting_period_to'))->endOfDay(),
                'min_receiptable' => request('min_receiptable'),
                'receipt_date' => fromLocal(request('receipt_date')),
                'status' => request('status'),
                'tax_receipt_template_id' => request('tax_receipt_template_id'),
                'auto_notify' => request('auto_notify'),
            ]));

            $this->flash->success('Consolidated receipting batch job has started.');
        } catch (DisclosableException $e) {
            $this->flash->error($e->getMessage());
        }

        return redirect('jpanel/tax_receipts');
    }

    /**
     * @return string
     */
    public function bulkAction()
    {
        user()->canOrRedirect('taxreceipt.edit');

        set_time_limit(300);

        if (request('ids')) {
            $query = TaxReceipt::whereIn('id', request('ids'));
        } else {
            $query = $this->_baseQueryWithFilters();
        }

        $taxReceipts = $query->cursor();

        switch (request('action')) {
            case 'issue': return $this->bulkIssue($taxReceipts);
            case 'issue_and_notify': return $this->bulkIssue($taxReceipts, true);
            case 'void': return $this->bulkVoid($taxReceipts);
            case 'notify': return $this->bulkNotify($taxReceipts);
            case 'print': return $this->bulkPrint($taxReceipts);
        }

        return 'Ok';
    }

    /**
     * @param \Illuminate\Support\LazyCollection $taxReceipts
     */
    private function bulkIssue(LazyCollection $taxReceipts, $notify = false)
    {
        $taxReceipts->each(function ($receipt) use ($notify) {
            if ($receipt->status === 'draft') {
                $receipt->issue();

                try {
                    if ($notify) {
                        $receipt->notify();
                    }
                } catch (MessageException $e) {
                    //
                }
            }
        });

        return 'Ok';
    }

    /**
     * @param \Illuminate\Support\LazyCollection $taxReceipts
     */
    private function bulkNotify(LazyCollection $taxReceipts)
    {
        $taxReceipts->each(function ($receipt) {
            if ($receipt->status === 'issued') {
                try {
                    $receipt->notify();
                } catch (MessageException $e) {
                    //
                }
            }
        });

        return 'Ok';
    }

    /**
     * @param \Illuminate\Support\LazyCollection $taxReceipts
     */
    private function bulkVoid(LazyCollection $taxReceipts)
    {
        $taxReceipts->each->void();

        return 'Ok';
    }

    /**
     * @param \Illuminate\Support\LazyCollection $taxReceipts
     */
    private function bulkPrint(LazyCollection $taxReceipts)
    {
        $html = [];

        foreach ($taxReceipts as $receipt) {
            $html[] = $receipt->toHtmlTemplate();
        }

        $html = implode('<p style="page-break-after: always;"/><br>', $html);

        return app('pdf')->loadHtml($html);
    }
}
