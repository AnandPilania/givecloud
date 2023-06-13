<?php

namespace Ds\Domain\DoubleTheDonation\Jobs;

use Ds\Domain\DoubleTheDonation\DoubleTheDonationService;
use Ds\Jobs\Job;
use Ds\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class RegisterDonation extends Job implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /** @var \Ds\Models\Order */
    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function handle(): void
    {
        app(DoubleTheDonationService::class)->registerOrder($this->order);
    }

    public function shouldQueue(): bool
    {
        return $this->featureIsEnabled()
            && $this->shouldRegisterOrder();
    }

    public function featureIsEnabled(): bool
    {
        return feature('double_the_donation')
            && sys_get('double_the_donation_enabled');
    }

    public function shouldRegisterOrder(): bool
    {
        if (sys_get('bool:double_the_donation_sync_all_contributions')) {
            return true;
        }

        return (bool) $this->order->doublethedonation_entered_text;
    }
}
