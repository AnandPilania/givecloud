<?php

namespace Ds\Listeners\Order;

use Ds\Events\OrderWasCompleted;
use Ds\Services\Order\OrderEmailPreferencesService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendNotificationEmails implements ShouldQueue
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
        if (! $event->order->send_confirmation_emails) {
            return;
        }

        if (app(OrderEmailPreferencesService::class)->shouldSendLegacyEmail($event->order)) {
            cart_send_customer_email($event->order->client_uuid);
        }

        cart_send_site_owner_email($event->order->client_uuid);

        $event->order->notify();

        cart_send_downloads($event->order->client_uuid);
    }
}
