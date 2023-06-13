<?php

namespace Ds\Http\Controllers\Reports;

use Carbon\Carbon;
use Ds\Domain\Commerce\Currency;
use Ds\Domain\Commerce\GatewayFactory;
use Ds\Domain\Shared\DataTable;
use Ds\Http\Controllers\Controller;
use Ds\Models\AccountType;
use Ds\Models\Membership;
use Ds\Models\Payment;
use Ds\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use LiveControl\EloquentDataTable\ExpressionWithName;

class PaymentsController extends Controller
{
    public function index()
    {
        $fundraisingForms = Product::donationForms()
            ->orderBy('name')
            ->pluck('name', 'id')
            ->mapWithKeys(fn ($v, $k) => [Product::getHashids()->encode($k) => $v]);

        $paymentTypes = collect(Payment::getDistinctValuesOf('type'))
            ->mapWithKeys(fn ($v) => [$v => ucfirst($v)])
            ->merge([
                'apple_pay' => 'Apple Pay',
                'google_pay' => 'Google Pay',
            ])->sort();

        return $this->getView('reports/payments', [
            'pageTitle' => 'Payments',
            'account_types' => AccountType::all(),
            'currencies' => Currency::getLocalCurrencies(),
            'fundraisingForms' => $fundraisingForms,
            'memberships' => Membership::all(),
            'paymentTypes' => $paymentTypes,
            'firstOfYear' => toUtcFormat(today()->subMonthNoOverflow(), 'M j, Y'),
            'today' => toUtcFormat(Carbon::today(), 'M j, Y'),
            '__menu' => 'reports.payments',
        ]);
    }

    public function get()
    {
        $payments = $this->_base_query();

        $dataTable = new DataTable($payments, [
            new ExpressionWithName('payments.created_at', 'created_at'),
            new ExpressionWithName('payments.captured_at', 'captured_at'),
            new ExpressionWithName('payments.reference_number', 'reference_number'),
            new ExpressionWithName('payments.amount', 'amount'),
            new ExpressionWithName('(case when payments.bank_last4 is not null then payments.bank_name else payments.card_name end)', 'card_name'),
            'gateway_type',
            'card_brand',
            'card_last4',
            new ExpressionWithName('payments.description', 'description'),
            new ExpressionWithName('payments.failure_message', 'failure_message'),

            new ExpressionWithName('payments.amount_refunded', 'amount_refunded'),
            new ExpressionWithName('payments_pivot.order_id', 'order_id'),
            new ExpressionWithName('payments_pivot.recurring_payment_profile_id', 'recurring_payment_profile_id'),

            new ExpressionWithName('payments.currency', 'currency'),
            new ExpressionWithName('payments.card_cvc_check', 'card_cvc_check'),
            new ExpressionWithName('payments.card_address_line1_check', 'card_address_line1_check'),
            new ExpressionWithName('payments.card_address_zip_check', 'card_address_zip_check'),
            new ExpressionWithName('payments.type', 'type'),
            new ExpressionWithName('payments.bank_account_holder_type', 'bank_account_holder_type'),
            new ExpressionWithName('payments.bank_account_type', 'bank_account_type'),
            new ExpressionWithName('payments.cheque_number', 'cheque_number'),
            'source_account_id',
            new ExpressionWithName('payments.refunded', 'refunded'),
        ]);

        $dataTable->setFormatRowFunction(function ($row) {
            if (Str::startsWith($row->description, 'Payment for Contribution')) {
                $links = preg_replace('/^Payment for Contributions? /', '', $row->description);
                $links = collect(explode(',', $links))->map(function ($id) {
                    $id = trim($id, '# ');

                    return sprintf('<a href="%s">#%s</a>', e(route('backend.orders.edit_without_id', ['c' => $id])), e($id));
                });
                $description = Str::plural('Payment for Contribution', count($links)) . ' ' . $links->implode(', ');
            } elseif ($row->order_id) {
                $description = sprintf('<a href="%s">%s</a>', e(route('backend.orders.edit', $row->order_id)), e($row->description));
            } elseif (Str::startsWith($row->description, 'Payment for Recurring Payment Profile')) {
                $links = preg_replace('/^Payment for Recurring Payment Profiles? /', '', $row->description);
                $links = collect(explode(',', $links))->map(function ($id) {
                    $id = trim($id, '# ');

                    return sprintf('<a href="/jpanel/recurring_payments/%s">#%s</a>', e($id), e($id));
                });
                $description = Str::plural('Payment for Recurring Payment Profile', count($links)) . ' ' . $links->implode(', ');
            } elseif ($row->recurring_payment_profile_id) {
                $description = sprintf('<a href="/jpanel/recurring_payments?id=%s">%s</a>', e($row->recurring_payment_profile_id), e($row->description));
            } else {
                $description = e($row->description);
            }

            if ($row->refunded) {
                $amount = sprintf(
                    '<span class="text-muted"><i class="fa fa-reply"></i>&nbsp;%s</span>',
                    e(money($row->amount_refunded, $row->currency)->format('0,0.00 $$$'))
                );
            } elseif ($row->amount_refunded) {
                $amount = sprintf(
                    '<span class="text-muted"><i class="fa fa-reply"></i>&nbsp;%s</span><br>%s',
                    e(money($row->amount_refunded, $row->currency)->format('0,0.00 $$$')),
                    e(money($row->amount - $row->amount_refunded, $row->currency)->format('0,0.00 $$$'))
                );
            } else {
                $amount = e(money($row->amount, $row->currency)->format('0,0.00 $$$'));
            }

            return [
                e(toLocalFormat($row->created_at, 'M j, Y h:i:s A')),
                e(toLocalFormat($row->captured_at, 'M j, Y h:i:s A')),
                e($row->reference_number),
                dangerouslyUseHTML($amount),
                e($row->orders[0]->billing_display_name ?? $row->account->display_name ?? $row->card_name),
                e($row->gateway_type),
                e($row->source_type),
                dangerouslyUseHTML($description),
                e(($row->verification_messages) ? implode(', ', $row->verification_messages) : 'Pass'),
                e($row->failure_message),
            ];
        });

        return response($dataTable->withManualCount()->make());
    }

    public function getAggregate(): string
    {
        $subQuery = $this->_base_query()
            ->select([
                'payments.gateway_type',
                DB::raw('(payments.amount - payments.amount_refunded) * payments.functional_exchange_rate as amount'),
                'payments.currency',
                DB::raw('CASE WHEN `payments`.`card_brand` IN ("Visa", "MasterCard") THEN `payments`.`card_brand`
                        WHEN `payments`.`card_brand` = "American Express" THEN "Amex"
                        WHEN `payments`.`type` = "bank" THEN "ACH"
                        ELSE "Other"
                        END as card_brand'),
            ]);

        $totals = DB::query()
            ->select('agg.gateway_type', 'agg.card_brand', 'agg.currency', DB::raw('sum(agg.amount) as amount'))
            ->fromSub($subQuery, 'agg')
            ->groupBy('agg.gateway_type', 'agg.card_brand', 'agg.currency')
            ->orderBy('agg.gateway_type')
            ->orderBy('agg.card_brand')
            ->get()
            ->map(function ($stat) {
                return [
                    'gateway_type' => $stat->gateway_type,
                    'card_brand' => $stat->card_brand,
                    'amount' => money($stat->amount, $stat->currency)->toCurrency(sys_get('dpo_currency'))->getAmount(),
                ];
            })->groupBy('gateway_type')
            ->map(function ($gatewayTotals) {
                return $gatewayTotals->groupBy('card_brand')->mapWithKeys(function ($o, $brand) {
                    return [$brand => $o->sum('amount')];
                });
            });

        $paymentMethods = [
            'MasterCard' => array_sum(array_column($totals->toArray(), 'MasterCard')),
            'Visa' => array_sum(array_column($totals->toArray(), 'Visa')),
            'Amex' => array_sum(array_column($totals->toArray(), 'Amex')),
            'ACH' => array_sum(array_column($totals->toArray(), 'ACH')),
            'Other' => array_sum(array_column($totals->toArray(), 'Other')),
        ];

        $gatewayNamesMap = $totals->keys()->mapWithKeys(function ($gatewayKey) {
            return [
                $gatewayKey => rescue(
                    fn () => GatewayFactory::create($gatewayKey)->getDisplayName(),
                    ucfirst($gatewayKey)
                ),
            ];
        });

        return view('reports.payments.breakdowns', compact('totals', 'paymentMethods', 'gatewayNamesMap'))->render();
    }

    public function export()
    {
        return response()->streamDownload(function () {
            $hasLocalCurrencies = Currency::hasLocalCurrencies();

            $fp = fopen('php://output', 'w');

            $data = [
                'Attempted',
                'Captured',
                'Transaction',
                'Amount',
                'Amount Refunded',
                'Currency',
            ];

            if ($hasLocalCurrencies) {
                $data = array_merge($data, [
                    'FX Rate',
                    currency() . ' Amount',
                ]);
            }

            $data = array_merge($data, [
                'Name on Card / Bank Account',
                'Gateway',
                'Method',
                'Reference',
                'Verification',
                'Fail Text',
            ]);

            fputcsv($fp, $data);

            $payments = $this->_base_query()
                ->select([
                    'payments.*',
                    DB::raw('(case when payments.bank_last4 is not null then payments.bank_name else payments.card_name end) as card_name'),
                ])->orderBy('payments.created_at', 'desc');

            $payments->chunk(250, function ($payments) use ($fp, $hasLocalCurrencies) {
                foreach ($payments as $payment) {
                    $data = [
                        toLocalFormat($payment->created_at, 'csv'),
                        toLocalFormat($payment->captured_at, 'csv'),
                        $payment->reference_number,
                        numeral($payment->amount),
                        $payment->amount_refunded ? numeral($payment->amount_refunded) : '',
                        $payment->currency,
                    ];

                    if ($hasLocalCurrencies) {
                        $data = array_merge($data, [
                            $payment->functional_exchange_rate,
                            numeral($payment->functional_total),
                        ]);
                    }

                    $data = array_merge($data, [
                        $payment->orders[0]->billing_display_name ?? $payment->account->display_name ?? $payment->card_name,
                        $payment->gateway_type,
                        $payment->source_type,
                        $payment->description,
                        ($payment->verification_messages) ? implode(', ', $payment->verification_messages) : 'Pass',
                        $payment->failure_message,
                    ]);

                    fputcsv($fp, $data);
                }
            });

            fclose($fp);
        }, 'payments.csv', [
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Content-Description' => 'File Transfer',
            'Content-type' => 'text/csv',
            'Expires' => '0',
            'Pragma' => 'public',
        ]);
    }

    private function _base_query()
    {
        $query = Payment::query()
            ->with([
                'orders',
                'account',
            ])->join('payments_pivot', 'payments_pivot.payment_id', '=', 'payments.id')
            ->leftJoin('recurring_payment_profiles as rpp', 'rpp.id', '=', 'payments_pivot.recurring_payment_profile_id')
            ->leftJoin('productorder as pay_orders', 'pay_orders.id', '=', 'payments_pivot.order_id')
            ->leftJoin('productorder as rpp_orders', 'rpp_orders.id', '=', 'rpp.productorder_id')
            ->leftJoin('productorderitem as pay_order_items', 'pay_orders.id', '=', 'pay_order_items.productorderid')
            ->leftJoin('productorderitem as rpp_order_items', 'rpp_orders.id', '=', 'rpp_order_items.productorderid')
            ->leftJoin('productinventory as pay_variants', 'pay_variants.id', 'pay_order_items.productinventoryid')
            ->leftJoin('productinventory as rpp_variants', 'rpp_variants.id', 'rpp_order_items.productinventoryid')
            ->whereNull('pay_orders.deleted_at')
            ->whereNull('rpp_orders.deleted_at')
            ->groupBy('payments.id');

        // search
        if (Str::startsWith(request('fk'), 'ref:')) {
            $refs = trim(substr(request('fk'), 4), '()');
            $refs = array_map('trim', explode(' or ', strtolower($refs)));
            $query->whereIn('payments.reference_number', $refs);
        } elseif (Str::startsWith(request('fk'), 'ip:')) {
            $ips = trim(substr(request('fk'), 3), '()');
            $ips = array_map('trim', explode(' or ', strtolower($ips)));
            $query->whereIn('payments.ip_address', $ips);
        } elseif (request('fk')) {
            $query->where(function ($q) {
                $q->where('payments.card_name', 'like', '%' . request('fk') . '%')
                    ->orWhere('payments.reference_number', 'like', '%' . request('fk') . '%')
                    ->orWhere('payments.card_address_line1', 'like', '%' . request('fk') . '%')
                    ->orWhere('payments.card_address_line2', 'like', '%' . request('fk') . '%')
                    ->orWhere('payments.card_address_city', 'like', '%' . request('fk') . '%')
                    ->orWhere('payments.card_address_state', 'like', '%' . request('fk') . '%')
                    ->orWhere('payments.card_address_zip', 'like', '%' . request('fk') . '%')
                    ->orWhere('payments.description', 'like', '%' . request('fk') . '%')
                    ->orWhere('payments.card_last4', 'like', '%' . request('fk') . '%')
                    ->orWhere('payments.bank_last4', 'like', '%' . request('fk') . '%');
            });
        }

        // paid
        if (request('fp') == 'paid') {
            $query->where('payments.paid', true);
            $query->where('payments.refunded', false);
        } elseif (request('fp') == 'refunded') {
            $query->where('payments.refunded', true);
        } elseif (request('fp') == 'failed') {
            $query->where('payments.status', 'failed');
        }

        if (request('fp') === 'spam') {
            $query->onlySpam();
        }

        // one-time / recurring
        if (request('fo') == 'onetime') {
            $query->whereNotNull('payments_pivot.order_id');
        } elseif (request('fo') == 'recurring') {
            $query->whereNotNull('payments_pivot.recurring_payment_profile_id');
        }

        if (request('fg')) {
            $query->where('payments.gateway_type', '=', request('fg'));
        }

        if (request('fm')) {
            $query->where('payments.card_brand', '=', request('fm'));
        }

        if (in_array(request('ft'), ['apple_pay', 'google_pay'], true)) {
            $query->where('payments.card_wallet', '=', request('ft'));
        } elseif (request('ft')) {
            $query->where('payments.type', '=', request('ft'));
        }

        if (request('fb') == 'unknown') {
            $query->whereNull('payments.bank_name');
        } elseif (request('fb')) {
            $query->where('payments.card_brand', '=', request('fb'));
        }

        if (request('ff') == 'unknown') {
            $query->whereNull('payments.failure_message');
        } elseif (request('ff')) {
            $query->where('payments.failure_message', '=', request('ff'));
        }

        if (request('fav') == 'pass') {
            $query->where(function ($q) {
                $q->where('payments.card_cvc_check', '=', 'pass');
                $q->where('payments.card_address_line1_check', '=', 'pass');
                $q->where('payments.card_address_zip_check', '=', 'pass');
            });
        } elseif (request('fav') == 'fail') {
            $query->where(function ($q) {
                $q->where('payments.card_cvc_check', '=', 'fail');
                $q->orWhere('payments.card_address_line1_check', '=', 'fail');
                $q->orWhere('payments.card_address_zip_check', '=', 'fail');
            });
        } elseif (request('fav') == 'unavailable') {
            $query->where(function ($q) {
                $q->whereNotIn('payments.card_cvc_check', ['pass', 'fail']);
                $q->whereNotIn('payments.card_address_line1_check', ['pass', 'fail']);
                $q->whereNotIn('payments.card_address_zip_check', ['pass', 'fail']);
            });
        } elseif (request('fav') == 'bad_cvc') {
            $query->where('payments.card_cvc_check', '=', 'fail');
        } elseif (request('fav') == 'no_cvc') {
            $query->whereNotIn('card_cvc_check', ['pass', 'fail']);
        } elseif (request('fav') == 'bad_address') {
            $query->where('payments.card_address_line1_check', '=', 'fail');
        } elseif (request('fav') == 'no_address') {
            $query->whereNotIn('payments.card_address_line1_check', ['pass', 'fail']);
        } elseif (request('fav') == 'bad_zip') {
            $query->where('payments.card_address_zip_check', '=', 'fail');
        } elseif (request('fav') == 'no_zip') {
            $query->whereNotIn('payments.card_address_zip_check', ['pass', 'fail']);
        }

        // ip_country
        if (request('foi')) {
            $query->where(DB::raw('IFNULL(pay_orders.ip_country,rpp_orders.ip_country)'), '=', request('foi'));
        }

        // referral source
        if (request('for')) {
            $query->where(DB::raw('IFNULL(pay_orders.referral_source,rpp_orders.referral_source)'), 'like', '%' . request('for') . '%');
        }

        // tracking source
        if (request('fots')) {
            $query->where(DB::raw('IFNULL(pay_orders.tracking_source,rpp_orders.tracking_source)'), 'like', '%' . request('fots') . '%');
        }

        // tracking medium
        if (request('fotm')) {
            $query->where(DB::raw('IFNULL(pay_orders.tracking_medium,rpp_orders.tracking_medium)'), 'like', '%' . request('fotm') . '%');
        }

        // tracking content
        if (request('fotc')) {
            $query->where(DB::raw('IFNULL(pay_orders.tracking_campaign,rpp_orders.tracking_campaign)'), 'like', '%' . request('fotc') . '%');
        }

        // tracking content
        if (request('fott')) {
            $query->where(DB::raw('IFNULL(pay_orders.tracking_content,rpp_orders.tracking_content)'), 'like', '%' . request('fott') . '%');
        }

        // capture date
        $fd1 = fromLocal(request('fd1'));
        $fd2 = fromLocal(request('fd2'));
        if ($fd1 && $fd2) {
            $query->whereBetween('payments.captured_at', [
                toUtc($fd1->startOfDay()),
                toUtc($fd2->endOfDay()),
            ]);
        } elseif ($fd1) {
            $query->where('payments.captured_at', '>=', toUtc($fd1->startOfDay()));
        } elseif ($fd2) {
            $query->where('payments.captured_at', '<=', toUtc($fd2->endOfDay()));
        }

        // created (attmpted) date
        $fc1 = fromLocal(request('fc1'));
        $fc2 = fromLocal(request('fc2'));
        if ($fc1 && $fc2) {
            $query->whereBetween('payments.created_at', [
                toUtc($fc1->startOfDay()),
                toUtc($fc2->endOfDay()),
            ]);
        } elseif ($fc1) {
            $query->where('payments.created_at', '>=', toUtc($fc1->startOfDay()));
        } elseif ($fc2) {
            $query->where('payments.created_at', '<=', toUtc($fc2->endOfDay()));
        }

        $currency_code = request('cc');
        if ($currency_code) {
            $query->where('payments.currency', $currency_code);
        }

        $account_type = request('fat');
        if ($account_type) {
            $query->whereHas('account', function ($query) use ($account_type) {
                $query->where('account_type_id', $account_type);
            });
        }

        request()->whenFilled('fundraising_forms', function ($ids) use ($query) {
            $ids = collect(explode(',', $ids))->map(fn ($v) => Product::decodeHashid($v));

            $query->where(function ($query) use ($ids) {
                $query->whereIn('pay_variants.productid', $ids);
                $query->orWhereIn('rpp_variants.productid', $ids);
            });
        });

        // memberships
        if (request('fmm')) {
            $query->leftJoin('group_account as fmGA', 'fmGA.account_id', 'payments.source_account_id');
            $query->leftJoin('membership as fmG', 'fmG.id', 'fmGA.group_id');
            $query->where('fmGA.group_id', request('fmm'));
            $query->where(function ($query) {
                $query->whereNull('fmGA.start_date');
                $query->orWhere('fmGA.start_date', '<=', fromLocal('today'));
            });
            $query->where(function ($query) {
                $query->whereNull('fmGA.end_date');
                $query->orWhere('fmGA.end_date', '>=', fromLocal('today'));
            });
        }

        return $query;
    }
}
