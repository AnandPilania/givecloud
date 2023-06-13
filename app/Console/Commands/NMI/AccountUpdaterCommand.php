<?php

namespace Ds\Console\Commands\NMI;

use Carbon\Carbon;
use Ds\Domain\Commerce\ACH;
use Ds\Domain\Commerce\Models\PaymentProvider;
use Ds\Enums\RecurringPaymentProfileStatus;
use Ds\Models\PaymentMethod;
use Ds\Models\RecurringPaymentProfile;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AccountUpdaterCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'nmi:accountupdater';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks for updated expiry information on expired payment methods.';

    /** @var \Ds\Illuminate\Console\ProgressBar */
    protected $bar;

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $provider = PaymentProvider::query()
            ->where('enabled', true)
            ->whereIn('provider', ['nmi', 'safesave'])
            ->first();

        if (! $provider || ! $provider->config('account_updater_enabled')) {
            return;
        }

        $shouldReactivateSuspendedRpps = sys_get('bool:account_updater_reactivate_suspended');

        $vaults = $provider->gateway->getAccountUpdaterCustomerVaults('1 month ago');

        $this->bar = $this->createProgressBar(count($vaults));

        foreach ($vaults as $vault) {
            try {
                $paymentMethod = PaymentMethod::query()
                    ->where('payment_provider_id', $provider->id)
                    ->where('token', $vault['customer_vault_id'])
                    ->firstOrFail();
            } catch (ModelNotFoundException $e) {
                $this->bar->advance();

                continue;
            }

            $this->bar->setMessage("Checking Payment Method (ID: {$paymentMethod->id})");

            $updated = $this->refreshPaymentMethod($paymentMethod, $vault);

            if ($shouldReactivateSuspendedRpps && $updated) {
                if ($paymentMethod->is_expired) {
                    $this->bar->advance();

                    continue;
                }

                $profiles = $paymentMethod->recurringPaymentProfiles()
                    ->where('status', RecurringPaymentProfileStatus::SUSPENDED)
                    ->get();

                foreach ($profiles as $profile) {
                    $this->reactivateProfile($profile);
                }
            }

            $this->bar->advance();
        }

        $this->bar->finish();
        $this->bar->newLine();
    }

    /**
     * Refreshes payment method with data from the customer vault.
     *
     * @param \Ds\Models\PaymentMethod $paymentMethod
     * @param array $data
     * @return bool
     */
    private function refreshPaymentMethod(PaymentMethod $paymentMethod, array $data)
    {
        // Update PMs with missing ACH routing data
        if ($data['check_aba'] && ! $paymentMethod->ach_routing) {
            $this->bar->comment("Updating #{$paymentMethod->id} (ACH)");

            $paymentMethod->ach_bank_name = ACH::getBankName($data['check_aba']);
            $paymentMethod->ach_entity_type = $data['account_holder_type'];
            $paymentMethod->ach_account_type = $data['account_type'];
            $paymentMethod->ach_routing = $data['check_aba'];
            $paymentMethod->save();

            return false;
        }

        // Skip ACH vaults
        if (empty($data['cc_number'])) {
            return false;
        }

        $data['cc_number'] = substr($data['cc_number'], -4);

        $last4 = $paymentMethod->account_last_four;
        $expiry = fromUtcFormat($paymentMethod->cc_expiry, 'my');

        // Only update payment method if the card number or expiry has changed
        if ($last4 !== $data['cc_number'] || $expiry !== $data['cc_exp']) {
            $this->bar->comment("Updating #{$paymentMethod->id}");

            $paymentMethod->account_last_four = $data['cc_number'];
            $paymentMethod->cc_expiry = $data['cc_exp'] ? Carbon::createFromFormat('my', $data['cc_exp'])->startOfMonth() : null;
            $paymentMethod->save();

            return true;
        }

        return false;
    }

    /**
     * Reactive a suspended payment profile.
     *
     * @param \Ds\Models\RecurringPaymentProfile $profile
     * @return bool
     */
    private function reactivateProfile(RecurringPaymentProfile $profile)
    {
        // Skip profiles that aren't suspended
        if ($profile->status !== RecurringPaymentProfileStatus::SUSPENDED) {
            return false;
        }

        // Skip profiles without payment methods
        if (! $profile->paymentMethod) {
            return false;
        }

        // Skip payment methods that are expired
        if ($profile->paymentMethod->is_expired) {
            return false;
        }

        // Skip profiles with no previous payments
        if (! $profile->last_payment_date) {
            return false;
        }

        // Skip profiles where the last payment was more than X days ago
        if (sys_get('int:account_updater_max_last_payment_days_ago') < $profile->last_payment_date->diffInDays()) {
            return false;
        }

        $this->bar->info(">> Reactivating profile #{$profile->id}");

        $profile->activateProfile();

        return true;
    }
}
