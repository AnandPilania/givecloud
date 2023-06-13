<?php

namespace Ds\Listeners\Order;

use Ds\Events\OrderWasCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Str;

class TrackInGoogleAnalytics implements ShouldQueue
{
    use Queueable;

    /**
     * Handle the event.
     *
     * @param \Ds\Events\OrderWasCompleted $event
     * @return void
     */
    public function handle(OrderWasCompleted $event)
    {
        if (! sys_get('webStatsPropertyId')) {
            return;
        }

        // only transactions made on the website should be tracked
        if ($event->order->source !== 'Web') {
            return;
        }

        $cid = $event->order->ga_client_id ?? Str::random(32);

        google_analytics_event([
            'cid' => $cid,
            't' => 'transaction',                            // Transaction hit type.
            'ti' => $event->order->invoicenumber,             // transaction ID. Required.
            'ta' => 'DS',                                     // Transaction affiliation.
            'tr' => round($event->order->totalamount, 2),     // Transaction revenue.
            'ts' => round($event->order->shipping_amount, 2), // Transaction shipping.
            'tt' => round($event->order->taxtotal, 2),        // Transaction tax.
            'cu' => $event->order->currency_code,             // Currency code.
            'uip' => $event->order->client_ip,
            'ua' => $event->order->client_browser,
        ]);

        foreach ($event->order->items()->with('variant.product')->get() as $item) {
            if ($item->sponsorship_id) {
                $variantname = '';
            } else {
                $variantname = $item->variant->variantname;
            }

            google_analytics_event([
                'cid' => $cid,
                't' => 'item',                         // Transaction hit type.
                'ti' => $event->order->invoicenumber,   // transaction ID. Required.
                'in' => $item->description,             // Item name. Required.
                'ip' => round($item->price, 2),         // Item price.
                'iq' => $item->qty,                     // Item quantity.
                'ic' => $item->reference,               // Item code / SKU.
                'iv' => $variantname,                   // Item variation / category.
                'cu' => $event->order->currency_code,   // Currency code.
                'uip' => $event->order->client_ip,
                'ua' => $event->order->client_browser,
            ]);
        }
    }

    public function viaQueue()
    {
        return 'low';
    }
}
