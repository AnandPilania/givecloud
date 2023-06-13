<?php

namespace Ds\Jobs\Orders;

use Ds\Mail\SupporterContributionAcknowledgment;
use Ds\Models\Order;
use Ds\Services\LocaleService;
use Ds\Services\Order\OrderEmailPreferencesService;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class SendSupporterContributionAcknowledgmentMail
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    private Order $order;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (! app(OrderEmailPreferencesService::class)->shouldSendModernEmail($this->order)) {
            return;
        }

        Mail::to(trim($this->order->billingemail))
            ->locale(
                $this->order->language
                ?? optional($this->order->member)->language
                ?? app(LocaleService::class)->siteLocale()
            )
            ->queue(new SupporterContributionAcknowledgment($this->order));
    }
}
