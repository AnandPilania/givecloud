<?php

namespace Ds\Domain\Commerce\Jobs\Webhooks;

use Ds\Jobs\Job;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use PayPal\IPN\PPIPNMessage;

class PayPalIpn extends Job implements ShouldQueue
{
    use InteractsWithQueue;
    use SerializesModels;

    /** @var \PayPal\IPN\PPIPNMessage */
    protected $ipn;

    /**
     * Create a new job instance.
     *
     * @param \PayPal\IPN\PPIPNMessage $ipn
     * @return void
     */
    public function __construct(PPIPNMessage $ipn)
    {
        $this->ipn = $ipn;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Log::debug('PayPal IPN', ['data' => json_encode($this->ipn->getRawData())]);

        /*
            recurring_payment
                payer_id
                payer_email
                recurring_payment_id
                payment_date
                payment_status
                payment_cycle
                product_name
                next_payment_date
                period_type
                currency_code
                initial_payment_amount
                amount
                amount_per_cycle
                txn_id
                first_name
                last_name

            subscr_payment
        */
    }
}
