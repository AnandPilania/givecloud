<?php

namespace Ds\Http\Controllers\Reports;

use Ds\Domain\Shared\DataTable;
use Ds\Http\Controllers\Controller;
use Ds\Models\AccountType;
use Ds\Models\Transaction;
use LiveControl\EloquentDataTable\ExpressionWithName;

class TransactionController extends Controller
{
    public function index()
    {
        user()->canOrRedirect('transaction');

        $filters = (object) [];
        $filters->search = request()->input('search');
        $filters->status = request()->input('status');
        $filters->payment_status = request()->input('payment_status');
        $filters->ordertime_str = request()->input('ordertime_str');
        $filters->ordertime_end = request()->input('ordertime_end');
        $filters->amt_str = request()->input('amt_str');
        $filters->amt_end = request()->input('amt_end');
        $filters->unsynced = request()->input('unsynced');
        $filters->account_type = request()->input('account_type');

        return view('reports.transactions', [
            '__menu' => 'recurring.transactions',
            'pageTitle' => ($filters->unsynced == 1) ? 'Unsynced Transactions' : 'Transaction History',
            'filters' => $filters,
            'account_types' => AccountType::all(),
            'unsynced_count' => Transaction::unsynced()->count(),
        ]);
    }

    public function export()
    {
        return $this->get('csv');
    }

    public function get($request_type = 'ajax')
    {
        user()->canOrRedirect('transaction');

        $transactions = Transaction::query();

        $filters = (object) [];
        $filters->search = request('search');
        if ($filters->search) {
            $keywords = array_map('trim', explode(' ', $filters->search));
            foreach ($keywords as $keyword) {
                $transactions->where(function ($query) use ($keyword) {
                    $query->where('reason_code', 'LIKE', "%{$keyword}%");
                    $query->orWhere('transaction_id', 'LIKE', "%{$keyword}%");
                    $query->orWhereRaw(
                        "recurring_payment_profile_id in (select r.id
                        from `member` as m
                        inner join `recurring_payment_profiles` as r on r.member_id = m.id
                        where r.id = `transactions`.recurring_payment_profile_id
                            and concat(m.first_name,' ',m.last_name) like ? or m.email like ?)",
                        ['%' . $keyword . '%', '%' . $keyword . '%']
                    );
                });
            }
        }

        $filters->payment_status = request('payment_status');
        if ($filters->payment_status == 'success') {
            $transactions->succeeded();
        } elseif ($filters->payment_status == 'fail') {
            $transactions->failed();
        } elseif ($filters->payment_status == 'refunded') {
            $transactions->refunded();
        }

        $filters->unsynced = request('unsynced');
        if ($filters->unsynced === '1') {
            $transactions->unsynced();
        } elseif ($filters->unsynced === '0') {
            $transactions->synced();
        }

        $filters->account_type = request('account_type');
        if ($filters->account_type) {
            $transactions->whereHas('recurringPaymentProfile.member', function ($query) use ($filters) {
                $query->where('account_type_id', $filters->account_type);
            });
        }

        // make sure that date filtering is inclusive
        $filters->ordertime_str = fromLocal(request('ordertime_str'));
        $filters->ordertime_end = fromLocal(request('ordertime_end'));
        if ($filters->ordertime_str && $filters->ordertime_end) {
            $transactions->whereBetween('order_time', [
                toUtc($filters->ordertime_str->startOfDay()),
                toUtc($filters->ordertime_end->endOfDay()),
            ]);
        } elseif ($filters->ordertime_str) {
            $transactions->where('order_time', '>=', toUtc($filters->ordertime_str->startOfDay()));
        } elseif ($filters->ordertime_end) {
            $transactions->where('order_time', '<=', toUtc($filters->ordertime_end->endOfDay()));
        }

        // make sure that date filtering is inclusive
        $filters->amt_str = request('amt_str');
        $filters->amt_end = request('amt_end');
        if ($filters->amt_str && $filters->amt_end) {
            $transactions->whereBetween('amt', [
                $filters->amt_str,
                $filters->amt_end,
            ]);
        } elseif ($filters->amt_str) {
            $transactions->where('amt', '>=', $filters->amt_str);
        } elseif ($filters->amt_end) {
            $transactions->where('amt', '<=', $filters->amt_end);
        }

        $transactions->with('recurringPaymentProfile.member');
        $transactions->with('paymentMethod');

        // CSV
        if ($request_type === 'csv') {
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Content-Description: File Transfer');
            header('Content-type: text/csv');
            header('Content-Disposition: attachment; filename=transactions.csv');
            header('Expires: 0');
            header('Pragma: public');
            $out_file = fopen('php://output', 'w');
            fputcsv($out_file, ['ID', 'Transaction ID', 'Date', 'Status', 'Response', 'Amount', 'Currency', 'DP Gift ID', 'Profile', 'Supporter Name', 'Supporter Email', 'DP Donor ID', 'Payment Method', 'Last Log Entry', 'Refunded Amount', 'Refunded At', 'Refunded Auth', 'Tax Receipt Number']);
            $transactions->orderBy('id')->chunk(1000, function ($chunk) use (&$out_file) {
                foreach ($chunk as $txn) {
                    fputcsv($out_file, [
                        $txn->id,
                        $txn->transaction_id,
                        toLocalFormat($txn->order_time, 'csv'),
                        $txn->payment_status,
                        $txn->reason_code,
                        number_format($txn->amt, 2),
                        $txn->currency_code,
                        $txn->dpo_gift_id,
                        $txn->recurringPaymentProfile->profile_id,
                        $txn->recurringPaymentProfile->member->display_name,
                        $txn->recurringPaymentProfile->member->email,
                        $txn->recurringPaymentProfile->member->donor_id,
                        $txn->paymentMethod->display_name,
                        $txn->last_transaction_log,
                        $txn->refunded_amt,
                        toLocalFormat($txn->refunded_at, 'csv'),
                        $txn->refunded_auth,
                        $txn->taxReceipt->number ?? '',
                    ]);
                }
            });
            fclose($out_file);
            exit;
        }

        // generate data table
        $dataTable = new DataTable($transactions, [
            'id',
            'order_time',
            'transaction_id',
            'recurring_payment_profile_id',
            'payment_status',
            'reason_code',
            'amt',
            'refunded_amt',
            'dpo_gift_id',
            'dp_auto_sync',
            new ExpressionWithName('payment_status', 'col11'),
            'currency_code',
            'payment_method_id',
        ]);

        $dataTable->setFormatRowFunction(function ($txn) {
            return [
                dangerouslyUseHTML('<a href="#" class="ds-txn" data-txn-id="' . e($txn->id) . '"><i class="fa fa-search"></i></a>'),
                dangerouslyUseHTML(e(toLocalFormat($txn->order_time)) . ' <small class="text-muted">' . e(toLocal($txn->order_time)->format('g:iA')) . '</small>'),
                dangerouslyUseHTML(($txn->recurringPaymentProfile->member) ? '<a href="' . e(route('backend.member.edit', $txn->recurringPaymentProfile->member->id)) . '"><i class="fa fa-user"></i> ' . e($txn->recurringPaymentProfile->member->display_name) . '</a>' : ''),
                dangerouslyUseHTML(e($txn->recurringPaymentProfile->description) . ' <small><a href="/jpanel/recurring_payments/' . e($txn->recurringPaymentProfile->profile_id) . '">' . e($txn->recurringPaymentProfile->profile_id) . '</a></small>'),
                dangerouslyUseHTML('<i class="fa ' . e($txn->paymentMethod->fa_icon) . '"></i> ' . e($txn->paymentMethod->account_last_four)),
                e($txn->transaction_id ?? ''),
                e($txn->payment_status ?? ''),
                e($txn->reason_code ?? ''),
                dangerouslyUseHTML('<div class="stat-val">' . (($txn->refunded_amt > 0) ? '<i class="fa fa-fw fa-reply"></i>' : '') . e(number_format($txn->amt, 2)) . '&nbsp;<span class="text-muted">' . e($txn->currency_code) . '</span></div>'),
                e(number_format($txn->refunded_amt, 2)),
                e($txn->is_unsynced),
                e($txn->is_payment_accepted),
            ];
        });

        // return datatable JSON
        return response($dataTable->make());
    }
}
