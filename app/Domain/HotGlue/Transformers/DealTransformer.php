<?php

namespace Ds\Domain\HotGlue\Transformers;

use Ds\Models\Order;
use League\Fractal\TransformerAbstract;

/**
 * Unified Deal Transformer.
 *
 * @see https://hotglue.com/docs/unified/crm#deals
 */
class DealTransformer extends TransformerAbstract
{
    public function transform(Order $order): array
    {
        return [
            'type' => 'Contribution',
            'title' => 'Contribution #' . $order->invoicenumber ?: null,
            'close_date' => $order->ordered_at->toApiFormat(),
            'company_name' => optional($order->member)->bill_organization_name,
            'currency' => $order->currency_code,
            'deleted' => $order->deleted_at !== null,
            'expected_revenue' => $order->balance_amt * 100,
            'monetary_amount' => $order->balance_amt,
            'contact_email' => optional($order->member)->bill_email ?? $order->billingemail,
            //'pipeline_stage_id' => 'Closed Won',
            'status' => 'Open',
            'win_probability' => 100,
        ];
    }
}
