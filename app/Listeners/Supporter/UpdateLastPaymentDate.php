<?php

namespace Ds\Listeners\Supporter;

use Ds\Events\OrderWasCompleted;
use Ds\Events\RecurringPaymentWasCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;

class UpdateLastPaymentDate implements ShouldQueue
{
    /**
     * Handle the event.
     *
     * @param object $event
     * @return void
     */
    public function handle($event)
    {
        if ($event instanceof RecurringPaymentWasCompleted) {
            $supporter = $event->rpp->member;
        }

        if ($event instanceof OrderWasCompleted) {
            $supporter = $event->order->member;
        }

        if (! isset($supporter)) {
            return;
        }

        DB::table('member')
            ->where('id', $supporter->getKey())
            ->update([
                'first_payment_at' => DB::raw('(SELECT min(created_at) FROM payments WHERE paid = 1 AND source_account_id = member.id)'),
                'last_payment_at' => DB::raw('(SELECT max(created_at) FROM payments WHERE paid = 1 AND source_account_id = member.id)'),
            ]);
    }
}
