<?php

namespace Ds\Console\Commands\NMI;

use Ds\Domain\Commerce\Models\PaymentProvider;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;

class SetupLinkCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nmi:setuplink';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a kamikaze setup link.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $provider = PaymentProvider::provider('nmi')->firstOrFail();
        } catch (ModelNotFoundException $exception) {
            $provider = new PaymentProvider;
            $provider->enabled = false;
            $provider->provider = 'nmi';
            $provider->provider_type = 'credit';
            $provider->display_name = $provider->gateway->getDisplayName();
            $provider->test_mode = false;
            $provider->cards = 'amex,discover,mastercard,visa';
        }

        $token = strtolower(Str::random(9));
        $expires = now()->addHours(48)->format('U');

        $provider->config = ['setup_link' => $token . ':' . $expires];
        $provider->save();

        $this->info(secure_site_url("jpanel/onboard/nmi-setup/$token"));
    }
}
