<?php

namespace Ds\Http\Controllers\Reports;

use Ds\Domain\Commerce\Models\PaymentProvider;
use Ds\Http\Controllers\Controller;
use Ds\Models\Order;
use Ds\Models\Payment;
use Ds\Models\Transaction;

class SettlementController extends Controller
{
    public function index()
    {
        user()->canOrRedirect('reports.settlements');

        pageSetup('Settlement Batches', 'jpanel');

        return $this->getView('reports/settlements', [
            '__menu' => 'reports.settlements',
            'date' => fromLocalFormat(request('date', 'yesterday'), 'Y-m-d'),
        ]);
    }

    public function get()
    {
        user()->canOrRedirect('reports.settlements');

        $mode = request('mode', 'order');
        $date = fromLocalFormat(request('date', 'yesterday'), 'date:Y-m-d');

        $settlements = cache()->remember("nmi:settlements:$date", now()->addMinutes(30), function () use ($date) {
            /** @var PaymentProvider<\Ds\Domain\Commerce\Gateways\NMIGateway> */
            $provider = PaymentProvider::query()
                ->where('enabled', true)
                ->whereIn('provider', ['nmi', 'safesave'])
                ->firstOrFail();

            return $provider->getSettlements($date);
        });

        $payments = Payment::query()
            ->whereIn('reference_number', $settlements->pluck('transaction_id'))
            ->get();

        $data = $settlements
            ->reject(function ($settlement) use ($payments) {
                return (bool) $payments->firstWhere('reference_number', $settlement['transaction_id']);
            })->map(function ($settlement) {
                return [
                    'batch_id' => $settlement['batch_id'],
                    'response' => $settlement['response_text'],
                    'reference_number' => $settlement['transaction_id'],
                    'type' => 'Non-Givecloud',
                    'amount' => number_format($settlement['amount'], 2),
                    'description' => 'Non-Givecloud',
                    'item_description' => 'This transaction happened outside of Givecloud.',
                    'account' => '',
                    'gl' => '',
                    'campaign' => '',
                    'solicit' => '',
                    'sub_solicit' => '',
                    'date' => $settlement['date'],
                ];
            });

        $noExternalTransactions = $data->isEmpty();

        foreach ($payments as $payment) {
            $account = e($payment->account->display_name ?? '');

            if ($payment->account->donor_id ?? false) {
                $account = sprintf('%s (Donor ID: %s)', $account, $payment->account->donor_id);
            }

            $models = collect()
                ->merge($payment->orders)
                ->merge($payment->transactions);

            foreach ($models as $model) {
                if ($model instanceof Order) {
                    $amount = $model->totalamount;
                    $description = sprintf('Contribution #%s', $model->client_uuid);
                    $gl = '';
                    $campaign = '';
                    $solicit = '';
                    $subsolicit = '';
                } elseif ($model instanceof Transaction) {
                    $item = $model->recurringPaymentProfile->order_item ?? null;
                    $amount = $model->subtotal_amount;
                    $description = sprintf(
                        'Transaction #%s: %s',
                        $model->id,
                        $item->description ?? ''
                    );
                    $gl = $item->gl_code ?? '';
                    $campaign = $item->sponsorship->meta2 ?? $item->variant->metadata->dp_campaign ?? $item->variant->product->meta2 ?? '';
                    $solicit = $item->sponsorship->meta3 ?? $item->variant->metadata->dp_solicit ?? $item->variant->product->meta3 ?? '';
                    $subsolicit = $item->sponsorship->meta4 ?? $item->variant->metadata->dp_subsolicit ?? $item->variant->product->meta4 ?? '';
                } else {
                    $amount = 0;
                    $description = '';
                    $gl = '';
                    $campaign = '';
                    $solicit = '';
                    $subsolicit = '';
                }

                $settlement = $settlements->firstWhere('transaction_id', $payment->reference_number);

                $record = [
                    'batch_id' => $settlement['batch_id'],
                    'response' => $settlement['response_text'],
                    'reference_number' => $settlement['transaction_id'],
                    'type' => 'Contribution',
                    'amount' => number_format($amount, 2),
                    'description' => trim($description),
                    'item_description' => '',
                    'account' => $account,
                    'gl' => $gl,
                    'campaign' => $campaign,
                    'solicit' => $solicit,
                    'sub_solicit' => $subsolicit,
                    'date' => $settlement['date'],
                ];

                if ($model instanceof Order && $mode === 'items') {
                    foreach ($model->items as $item) {
                        $data[] = array_merge($record, [
                            'type' => 'Item',
                            'amount' => number_format($item->total, 2),
                            'item_description' => $item->description,
                            'gl' => $item->gl_code ?? '',
                            'campaign' => $item->sponsorship->meta2 ?? $item->variant->metadata->dp_campaign ?? $item->variant->product->meta2 ?? null,
                            'solicit' => $item->sponsorship->meta3 ?? $item->variant->metadata->dp_solicit ?? $item->variant->product->meta3 ?? null,
                            'sub_solicit' => $item->sponsorship->meta4 ?? $item->variant->metadata->dp_subsolicit ?? $item->variant->product->meta4 ?? null,
                        ]);
                    }
                    if ($model->dcc_total_amount > 0) {
                        $data[] = array_merge($record, [
                            'type' => 'DCC',
                            'amount' => number_format($model->dcc_total_amount, 2),
                            'item_description' => 'DCC',
                            'gl' => sys_get('dp_dcc_gl'),
                            'campaign' => sys_get('dp_dcc_campaign'),
                            'solicit' => sys_get('dp_dcc_solicit'),
                            'sub_solicit' => sys_get('dp_dcc_subsolicit'),
                        ]);
                    }
                    if ($model->shipping_amount > 0) {
                        $data[] = array_merge($record, [
                            'type' => 'Shipping',
                            'amount' => number_format($model->shipping_amount, 2),
                            'item_description' => 'Shipping',
                            'gl' => sys_get('dp_shipping_gl'),
                            'campaign' => sys_get('dp_shipping_campaign'),
                            'solicit' => sys_get('dp_shipping_solicit'),
                            'sub_solicit' => sys_get('dp_shipping_subsolicit'),
                        ]);
                    }
                    if ($model->taxtotal > 0) {
                        $data[] = array_merge($record, [
                            'type' => 'Tax',
                            'amount' => number_format($model->taxtotal, 2),
                            'item_description' => 'Tax',
                            'gl' => sys_get('dp_tax_gl'),
                            'campaign' => sys_get('dp_tax_campaign'),
                            'solicit' => sys_get('dp_tax_solicit'),
                            'sub_solicit' => sys_get('dp_tax_subsolicit'),
                        ]);
                    }
                } elseif ($model instanceof Transaction && $mode === 'items') {
                    $data[] = $record;
                    if ($model->dcc_amount > 0) {
                        $data[] = array_merge($record, [
                            'type' => 'DCC',
                            'amount' => number_format($model->dcc_amount, 2),
                            'item_description' => 'DCC',
                            'gl' => sys_get('dp_dcc_gl'),
                            'campaign' => sys_get('dp_dcc_campaign'),
                            'solicit' => sys_get('dp_dcc_solicit'),
                            'sub_solicit' => sys_get('dp_dcc_subsolicit'),
                        ]);
                    }
                } else {
                    $data[] = $record;
                }
            }
        }

        return [
            'date' => $date,
            'data' => $data->sortBy('date')->values(),
            'draw' => time(),
            'recordsFiltered' => count($data),
            'recordsTotal' => count($data),
            'dash' => [
                'batch_date' => fromUtcFormat($date, 'l, M j, Y'),
                'batch_count' => $settlements->count(),
                'batch_total' => number_format($settlements->sum('amount'), 2),
            ],
            'no_external_transactions' => $noExternalTransactions,
        ];
    }

    public function export()
    {
        $result = $this->get();

        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Content-Description: File Transfer');
        header('Content-type: text/csv');
        header("Content-Disposition: attachment; filename=settlements-{$result['date']}.csv");
        header('Expires: 0');
        header('Pragma: public');

        $out_file = fopen('php://output', 'w');
        fputcsv($out_file, ['Batch ID', 'Date', 'Response', 'Ref #', 'Amount', 'Description', 'Item Description', 'Account', 'GL', 'Campaign', 'Solicit', 'Sub Solicit']);
        foreach ($result['data'] as $record) {
            fputcsv($out_file, [
                $record['batch_id'],
                $result['date'],
                $record['response'],
                $record['reference_number'],
                $record['amount'],
                $record['description'],
                $record['item_description'],
                $record['account'],
                $record['gl'],
                $record['campaign'],
                $record['solicit'],
                $record['subsolicit'],
            ]);
        }
        fclose($out_file);
        exit;
    }
}
