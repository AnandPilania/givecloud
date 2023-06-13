<?php

namespace Ds\Domain\Commerce\Jobs\Webhooks;

use Ds\Jobs\Job;
use Ds\Models\RecurringPaymentProfile;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Stripe\Event;

class Stripe extends Job implements ShouldQueue
{
    use InteractsWithQueue;
    use SerializesModels;

    /** @var array */
    protected $webhook;

    /**
     * Create a new job instance.
     *
     * @param \Stripe\Event $event
     * @return void
     */
    public function __construct(Event $event)
    {
        $this->webhook = [
            'type' => data_get($event, 'type'),
            'data' => data_get($event, 'data.object'),
        ];
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->webhook['type'] === Event::CUSTOMER_SUBSCRIPTION_DELETED) {
            $this->customerSubscriptionDeleted($this->webhook['data']);
        }
    }

    /**
     * Occurs whenever a customer's subscription ends.
     *
     * @param mixed $subscription
     */
    private function customerSubscriptionDeleted($subscription)
    {
        $rpp = RecurringPaymentProfile::query()
            ->where('is_manual', true)
            ->where('is_locked', true)
            ->where('stripe_subscription_id', $subscription->id)
            ->first();

        if ($rpp && $subscription->status === 'canceled') {
            $rpp->status = 'Cancelled';
            $rpp->final_payment_due_date = fromUtc($subscription->canceled_at);
            $rpp->save();
        }
    }
}
