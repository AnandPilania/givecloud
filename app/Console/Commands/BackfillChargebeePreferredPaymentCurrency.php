<?php

namespace Ds\Console\Commands;

use Ds\Repositories\ChargebeeRepository;
use Illuminate\Console\Command;

class BackfillChargebeePreferredPaymentCurrency extends Command
{
    protected $signature = 'backfill:chargebee:currency';

    protected $description = 'Backfills Chargebee\'s preferred currency for CAD customer without an active subscription.';

    public function handle()
    {
        if (sys_get('dpo_currency') !== 'CAD') {
            $this->info('Nothing to do here, currency is not CAD.');

            return 0;
        }

        if (! site()->client->customer_id) {
            $this->info('No ChargeBee customer id, bailing.');

            return 0;
        }

        if (! site()->isTrial()) {
            $this->info('Client is not in trial, can\'t touch this.');

            return 0;
        }

        if (app(ChargebeeRepository::class)->getSubscription()) {
            $this->info('Client has active subscription, can\'t touch this.');

            return 0;
        }

        app('chargebee')->updateCustomer(site()->client->customer_id, [
            'preferred_currency_code' => 'CAD',
        ]);

        $this->info('Done.');

        return 0;
    }
}
