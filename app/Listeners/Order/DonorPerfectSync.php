<?php

namespace Ds\Listeners\Order;

use Ds\Events\OrderWasCompleted;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\App;
use Throwable;

class DonorPerfectSync implements ShouldQueue
{
    use InteractsWithQueue;
    use Queueable;

    /** @var int */
    public $tries = 6;

    // Number of seconds before retrying the job
    public function backoff(): array
    {
        return [500, 1000, 1500, 2000];
    }

    public function handle(OrderWasCompleted $event): void
    {
        if (! dpo_is_enabled() || ! $event->order->dp_sync_order) {
            return;
        }

        try {
            app('Ds\Services\DonorPerfectService')->pushOrder($event->order);
        } catch (Throwable $e) {
            if (App::bound('exceptionist')) {
                app('exceptionist')->notifyException($e);
            }

            throw $e;
        }
    }

    public function viaQueue()
    {
        return 'low';
    }
}
