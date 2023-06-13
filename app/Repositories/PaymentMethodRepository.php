<?php

namespace Ds\Repositories;

use Carbon\Carbon;
use Ds\Domain\Commerce\ACH;
use Ds\Domain\Commerce\Gateways\NMIGateway;
use Ds\Domain\Commerce\Models\PaymentProvider;
use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Models\Member;
use Ds\Models\PaymentMethod;

class PaymentMethodRepository
{
    public function getFingerprintMatch(Member $supporter, PaymentProvider $paymentProvider, string $fingerprint): ?PaymentMethod
    {
        return $supporter->paymentMethods()
            ->where('status', 'ACTIVE')
            ->where('payment_provider_id', $paymentProvider->id)
            ->where('fingerprint', $fingerprint)
            ->orderByDesc('created_at')
            ->first();
    }

    /**
     * Create a payment method from an NMI Customer Vault Id.
     *
     * @param string $customerVaultId
     * @param \Ds\Models\Member $member
     * @return \Ds\Models\PaymentMethod
     */
    public function createFromVault($customerVaultId, Member $member)
    {
        if (empty($customerVaultId)) {
            throw new MessageException('No vault id reference was supplied.');
        }

        $provider = PaymentProvider::query()
            ->where('enabled', true)
            ->whereIn('provider', ['nmi', 'safesave'])
            ->first();

        if (! $provider) {
            throw new MessageException('No supported payment provider found.');
        }

        $paymentMethod = new PaymentMethod;
        $paymentMethod->member_id = $member->id;
        $paymentMethod->payment_provider_id = $provider->id;
        $paymentMethod->status = 'PENDING';
        $paymentMethod->token = $customerVaultId;
        $paymentMethod->save();

        return $this->refreshPaymentMethod($paymentMethod);
    }

    /**
     * Update a payment method using the vault ID
     * associated with the payment method.
     *
     * @param \Ds\Models\PaymentMethod $paymentMethod
     * @return \Ds\Models\PaymentMethod
     */
    public function updateFromVault(PaymentMethod $paymentMethod)
    {
        return $this->refreshPaymentMethod($paymentMethod);
    }

    /**
     * Refreshes payment method with data from the customer vault.
     *
     * @param \Ds\Models\PaymentMethod $paymentMethod
     * @return \Ds\Models\PaymentMethod
     */
    public function refreshPaymentMethod(PaymentMethod $paymentMethod)
    {
        if (! ($paymentMethod->paymentProvider && $paymentMethod->paymentProvider->gateway instanceof NMIGateway)) {
            throw new MessageException('No supported payment provider found.');
        }

        $data = $paymentMethod->paymentProvider->gateway->getCustomerVault($paymentMethod->token);

        if (! $data) {
            throw new MessageException('No vault record found.');
        }

        $paymentMethod->status = 'ACTIVE';
        $paymentMethod->billing_first_name = $data['first_name'];
        $paymentMethod->billing_last_name = $data['last_name'];
        $paymentMethod->billing_email = $data['email'];
        $paymentMethod->billing_address1 = $data['address_1'];
        $paymentMethod->billing_address2 = $data['address_2'];
        $paymentMethod->billing_city = $data['city'];
        $paymentMethod->billing_state = $data['state'];
        $paymentMethod->billing_postal = $data['postal_code'];
        $paymentMethod->billing_country = $data['country'];
        $paymentMethod->billing_phone = $data['phone'];

        if ($data['check_aba']) {
            $paymentMethod->account_type = trim(ucwords($data['account_holder_type'] . ' ' . $data['account_type']));
            $paymentMethod->account_last_four = substr($data['check_account'], -4);
            $paymentMethod->ach_bank_name = ACH::getBankName($data['check_aba']);
            $paymentMethod->ach_entity_type = $data['account_holder_type'];
            $paymentMethod->ach_account_type = $data['account_type'];
            $paymentMethod->ach_routing = $data['check_aba'];
        }

        if ($data['cc_number']) {
            $paymentMethod->account_type = ucwords(card_type_from_first_number($data['cc_number']));
            $paymentMethod->account_last_four = substr($data['cc_number'], -4);
            $paymentMethod->cc_expiry = $data['cc_exp'] ? Carbon::createFromFormat('my', $data['cc_exp'])->startOfMonth() : null;
        }

        if (empty($paymentMethod->display_name)) {
            $paymentMethod->display_name = $paymentMethod->account_type;
        }

        return tap($paymentMethod)->save();
    }
}
