<?php

namespace Ds\Http\Controllers;

use Ds\Domain\Shared\DataTable;
use Ds\Models\Tribute;
use Ds\Models\TributeType;
use LiveControl\EloquentDataTable\ExpressionWithName;

class TributesController extends Controller
{
    /**
     * View tribute list.
     */
    public function index()
    {
        // tributes
        user()->canOrRedirect('tribute');

        // return view
        return view('tributes.index', [
            'pageTitle' => 'Tributes',
            '__menu' => 'reports.tributes',
            'tributeTypes' => TributeType::active()->get(),
            'input' => (object) [
                'search' => request('search'),
                'is_sent' => request('is_sent'),
                'notify' => request('notify'),
                'type' => request('type'),
                'created_at_str' => request('created_at_str'),
                'created_at_end' => request('created_at_end'),
                'notify_at_str' => request('notify_at_str'),
                'notify_at_end' => request('notify_at_end'),
                'notified_at_str' => request('notified_at_str'),
                'notified_at_end' => request('notified_at_end'),
            ],
            'unsentCount' => Tribute::unsentLetters()->count(),
        ]);
    }

    /**
     * Ajax data for tribute list.
     */
    public function index_ajax()
    {
        // tributes
        user()->canOrRedirect('tribute');

        // generate data table
        $dataTable = new DataTable($this->_baseQueryWithFilters(), [
            'tributes.id',
            new ExpressionWithName('tributes.id', 'col2'),
            'tribute_types.label',
            'productorder.invoicenumber',
            'tributes.name',
            'tributes.notify',
            'tributes.notify_name',
            'tributes.amount',
            'tributes.created_at',
            'tributes.notify_at',
            'tributes.notified_at',
        ]);

        // format results
        $dataTable->setFormatRowFunction(function ($tribute) {
            return [
                dangerouslyUseHTML('<input type="checkbox" class="slave" name="selectedids" value="' . e($tribute->tributesId) . '" />'),
                dangerouslyUseHTML('<a href="#" class="ds-tribute" data-tribute-id="' . e($tribute->tributesId) . '"><i class="fa fa-search"></i></a>'),
                $tribute->productorderInvoicenumber
                    ? dangerouslyUseHTML('<a href="' . route('backend.orders.order_number', $tribute->productorderInvoicenumber) . '">' . e($tribute->productorderInvoicenumber) . '</a>')
                    : '',
                e($tribute->tributeTypesLabel),
                e($tribute->tributesName),
                e(ucwords($tribute->tributesNotify)),
                dangerouslyUseHTML($tribute->tributesNotifyName . (($tribute->tributesNotify && ! $tribute->tributesNotifiedAt) ? '&nbsp;<small class="pull-right"><span class="label label-default">UNSENT</span></small>' : '')),
                e(number_format($tribute->tributesAmount, 2)),
                e(toLocalFormat($tribute->tributesCreatedAt)),
                e(fromDateFormat($tribute->tributesNotifyAt)),
                e(fromDateFormat($tribute->tributesNotifiedAt)),
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
        // tributes
        user()->canOrRedirect('tribute');

        // generate data table
        $tributes = $this->_baseQueryWithFilters()
            ->select(
                'tributes.*',
                'productorder.billing_first_name as billing_first_name',
                'productorder.billing_last_name as billing_last_name',
                'productorder.billingemail as billing_email',
                'productorder.invoicenumber',
                'tribute_types.label'
            )
            ->with('tributeType', 'orderItem.order');

        // output CSV
        header('Content-type: text/csv');
        header('Content-type: text/plain');
        header('Cache-Control: no-store, no-cache');
        header('Content-Disposition: attachment; filename="tributes.csv"');
        $outstream = fopen('php://output', 'w');
        fputcsv($outstream, ['Contribution', 'Tribute Type', 'Tribute Name', 'Notify', 'Notify Name', 'Billing First Name', 'Billing Last Name', 'Billing Email', 'Amount', 'Created on', 'Notify on', 'Sent on', 'Notify Message', 'Notify Email', 'Notify Address', 'Notify City', 'Notify State', 'Notify Zip', 'Notify Country'], ',', '"');
        foreach ($tributes->cursor() as $tribute) {
            fputcsv($outstream, [
                $tribute->invoicenumber,
                $tribute->label,
                $tribute->name,
                $tribute->notify,
                $tribute->notify_name,
                $tribute->billing_first_name,
                $tribute->billing_last_name,
                $tribute->billing_email,
                number_format($tribute->amount, 2),
                toLocalFormat($tribute->created_at, 'csv'),
                toLocalFormat($tribute->notify_at, 'csv'),
                toLocalFormat($tribute->notified_at, 'csv'),
                $tribute->message,
                $tribute->notify_email,
                $tribute->notify_address,
                $tribute->notify_city,
                $tribute->notify_state,
                $tribute->notify_zip,
                $tribute->notify_country,
            ], ',', '"');
        }
        fclose($outstream);
        exit;
    }

    /**
     * CSV output
     */
    public function index_labels()
    {
        // tributes
        user()->canOrRedirect('tribute');

        // generate data table
        $tributes = $this->_baseQueryWithFilters()
            ->where('notify', '=', 'letter')
            ->select('tributes.*');

        // if there are a specific set of ids
        if (request()->filled('ids')) {
            $tributes->whereIn('tributes.id', explode(',', request('ids')));
        }

        // generate a labels pdf
        return response()->labelsPdf([
            'pageTitle' => 'Tributes',
            'labels' => $tributes->orderBy('tributes.id'),
        ]);
    }

    /**
     * View record as PDF
     */
    public function pdf($tribute_id)
    {
        // grab tribute
        $tribute = Tribute::withTrashed()->find($tribute_id);

        // permissions hack (withTrashed issues)
        if (! $tribute->userCan('view')) {
            abort(403);
        }

        $html = '<html><head><meta http-equiv="Content-Type" content="text/html; charset=utf-8"/></head><body>' . $tribute->getLetterBody() . '</body></html>';

        // return HTML
        return response()->protectedPdf($html);
    }

    /**
     * View modal
     */
    public function modal($tribute_id)
    {
        // grab tribute
        $tribute = Tribute::withTrashed()->with('orderItem.order')->find($tribute_id); // findWithPermission($tribute_id);

        // permissions hack (withTrashed issues)
        if ($tribute && ! $tribute->userCan('view')) {
            $tribute = false;
        }

        // disable parent views
        $this->setViewLayout(false);

        // return modal view
        return $this->getView('tributes/modal', [
            'tribute' => $tribute,
        ]);
    }

    /**
     * Destroy the tribute
     */
    public function destroy($tribute_id)
    {
        // grab receipt
        $tribute = Tribute::findWithPermission($tribute_id, 'edit');

        // void/delete the receipt
        $tribute->delete();

        // return updated receipt
        return response()->json($tribute);
    }

    /**
     * Notify the receipient of the tribute.
     */
    public function notify($tribute_id)
    {
        // grab tribute
        $tribute = tribute::findWithPermission($tribute_id);

        // notify
        $tribute->doNotification();

        // return updated tribute
        return response()->json($tribute);
    }

    /**
     * Edit the tribute
     */
    public function edit($tribute_id)
    {
        // update tribute
        $tribute = Tribute::findWithPermission($tribute_id, 'edit');
        $tribute->name = request('name');
        $tribute->message = request('message');
        $tribute->notify = request('notify');
        $tribute->notify_name = request('notify_name');
        $tribute->notify_email = request('notify_email');
        $tribute->notify_address = request('notify_address');
        $tribute->notify_city = request('notify_city');
        $tribute->notify_state = request('notify_state');
        $tribute->notify_zip = request('notify_zip');
        $tribute->notify_country = request('notify_country');
        $tribute->save();

        // return updated tribute
        return response()->json($tribute);
    }

    /**
     * Merge all unsent letters into a single PDF to be printed (one-per-page)
     */
    public function printUnsentLetters()
    {
        // make sure we have ids to process
        if (! request()->filled('ids')) {
            $this->flash->error('No tributes letters were printed.');

            return redirect()->back();
        }

        // increase timeout (3min)
        set_time_limit(3 * 60);

        // max letters
        $max_letters = 500; // 42;

        // find all unsent
        $batch_tributes = Tribute::whereIn('id', explode(',', request('ids')))->limit($max_letters)->get();
        $batch_count = Tribute::whereIn('id', explode(',', request('ids')))->count();

        // nothing to print
        if (! $batch_tributes) {
            $this->flash->error('No tribute letters were printed.');

            return redirect()->to('/jpanel/tributes');
        }

        // array of letters
        $master_pdf_body = '';

        // loop over each letter
        foreach ($batch_tributes as $tribute) {
            // append each tribute's body, wrapped in a page break div
            $master_pdf_body .= '<div style="page-break-before:always;">' .
                    $tribute->getLetterBody() .
                '</div>';
        }

        // if there are more than $max_letters unsent letters, add a page to the document that says so
        if ($batch_count > $max_letters) {
            $master_pdf_body .= '<div style="page-break-before:always;"><p>Maximum page output of ' . $max_letters . ' was reached. (' . ($batch_count - $max_letters) . ') letters remaining.</p></div>';
        }

        // output the pdf
        // return response()->pdf($master_pdf_body);
        return response($master_pdf_body);
    }

    /**
     * Send all unsent letters.
     */
    public function sendUnsentLetters()
    {
        // make sure we have ids to process
        if (! request()->filled('ids')) {
            $this->flash->error('No tributes were marked as sent.');

            return redirect()->back();
        }

        // find all unsent
        $batch_tributes = Tribute::whereIn('id', explode(',', request('ids')))->get();

        // nothing to print
        if (! $batch_tributes) {
            $this->flash->error('No tributes were marked as sent.');

            return redirect()->back();
        }

        // loop over each letter
        foreach ($batch_tributes as $tribute) {
            // mark as notified
            $tribute->doNotification();
        }

        // redirect w/ success
        $this->flash->success($batch_tributes->count() . ' tribute' . (($batch_tributes->count() !== 1) ? 's' : '') . ' successfully marked as sent.');

        return redirect()->back();
    }

    /**
     * Build a base query based on request filter params.
     * Allows us to reuse this for datatables, csv, etc...
     */
    private function _baseQueryWithFilters()
    {
        $tributes = Tribute::query();

        // stat columns
        $tributes->join('productorderitem', 'productorderitem.id', '=', 'tributes.order_item_id', 'left')
            ->join('productorder', 'productorder.id', '=', 'productorderitem.productorderid', 'left')
            ->join('tribute_types', 'tribute_types.id', '=', 'tributes.tribute_type_id');

        // build filter
        $filters = (object) [];

        // search
        $filters->search = request('search');
        if ($filters->search) {
            $tributes->where(function ($query) use ($filters) {
                $query->where('productorder.invoicenumber', 'like', "%$filters->search%");
                $query->orWhere('tributes.name', 'like', "%$filters->search%");
                $query->orWhere('tributes.notify_name', 'like', "%$filters->search%");
                $query->orWhere('tributes.notify_email', 'like', "%$filters->search%");
                $query->orWhere('tributes.notify_address', 'like', "%$filters->search%");
            });
        }

        // is_sent
        $filters->is_sent = request('is_sent');
        if ($filters->is_sent === '1') {
            $tributes->whereNotNull('notified_at');
        } elseif ($filters->is_sent === '0') {
            $tributes->whereNull('notified_at');
        }

        // letter/email
        $filters->notify = request('notify');
        if (in_array($filters->notify, ['email', 'letter'])) {
            $tributes->where('tributes.notify', '=', request('notify'));
        } elseif ($filters->notify === 'none') {
            $tributes->whereNull('tributes.notify');
        }

        // type
        $filters->type = request('type');
        if ($filters->type) {
            $tributes->where('tributes.tribute_type_id', '=', request('type'));
        }

        // created date
        $filters->created_at_str = fromLocal(request('created_at_str'));
        $filters->created_at_end = fromLocal(request('created_at_end'));
        if ($filters->created_at_str && $filters->created_at_end) {
            $tributes->whereBetween('tributes.created_at', [
                toUtc($filters->created_at_str->startOfDay()),
                toUtc($filters->created_at_end->endOfDay()),
            ]);
        } elseif ($filters->created_at_str != '') {
            $tributes->where('tributes.created_at', '>=', toUtc($filters->created_at_str)->startOfDay());
        } elseif ($filters->created_at_end != '') {
            $tributes->where('tributes.created_at', '<=', toUtc($filters->created_at_end)->endOfDay());
        }

        // notify date
        $filters->notify_at_str = fromLocal(request('notify_at_str'));
        $filters->notify_at_end = fromLocal(request('notify_at_end'));
        if ($filters->notify_at_str && $filters->notify_at_end) {
            $tributes->whereBetween('tributes.notify_at', [
                toUtc($filters->notify_at_str->startOfDay()),
                toUtc($filters->notify_at_end->endOfDay()),
            ]);
        } elseif ($filters->notify_at_str) {
            $tributes->where('tributes.notify_at', '>=', toUtc($filters->notify_at_str->startOfDay()));
        } elseif ($filters->notify_at_end) {
            $tributes->where('tributes.notify_at', '<=', toUtc($filters->notify_at_end->endOfDay()));
        }

        // notified date
        $filters->notified_at_str = fromLocal(request('notified_at_str'));
        $filters->notified_at_end = fromLocal(request('notified_at_end'));
        if ($filters->notified_at_str && $filters->notified_at_end) {
            $tributes->whereBetween('tributes.notified_at', [
                toUtc($filters->notified_at_str->startOfDay()),
                toUtc($filters->notified_at_end->endOfDay()),
            ]);
        } elseif ($filters->notified_at_str) {
            $tributes->where('tributes.notified_at', '>=', toUtc($filters->notified_at_str->startOfDay()));
        } elseif ($filters->notified_at_end) {
            $tributes->where('tributes.notified_at', '<=', toUtc($filters->notified_at_end->endOfDay()));
        }

        return $tributes;
    }
}
