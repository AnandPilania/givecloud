<?php

namespace Ds\Console\Commands\PayPal;

use Ds\Domain\Commerce\Models\PaymentProvider;
use Ds\Models\Payment;
use Illuminate\Console\Command;
use Throwable;

class ReconciliationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'paypal:reconciliation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reconsile pending PayPal payments.';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $provider = PaymentProvider::getPayPalProvider();

        if (! $provider) {
            $this->error('No PayPal payment provider found.');
            exit(1);
        }

        $query = Payment::query()
            ->where('type', 'paypal')
            ->where('status', 'pending')
            ->where('gateway_type', 'paypalexpress');

        $count = $query->count();

        if ($count === 0) {
            return;
        }

        $bar = $this->createProgressBar($count);

        foreach ($query->cursor() as $payment) {
            try {
                $bar->setMessage("processing payment #{$payment->id}");

                $provider->gateway->syncPaymentStatus($payment);
            } catch (Throwable $e) {
                $bar->error($e);
            }

            $bar->advance();
        }

        $bar->finish();
        $bar->newLine();
    }
}
