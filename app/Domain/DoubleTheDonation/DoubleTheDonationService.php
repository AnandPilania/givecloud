<?php

namespace Ds\Domain\DoubleTheDonation;

use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Models\Order;
use Illuminate\Support\Facades\Http;

class DoubleTheDonationService
{
    public const API_URL = 'https://doublethedonation.com/api/360matchpro-partners/v1/';

    public function test(): bool
    {
        $response = Http::get(static::API_URL . 'verify-360-keys', [
            '360matchpro_public_key' => sys_get('double_the_donation_public_key'),
            '360matchpro_private_key' => sys_get('double_the_donation_private_key'),
        ])->json();

        if (data_get($response, 'public_key_valid') === true
            && data_get($response, 'private_key_valid') === true
            && data_get($response, 'private_key_enabled') === true
            && data_get($response, 'subscription_status') === 'active') {
            return true;
        }

        throw new MessageException(data_get($response, 'note', 'An error occurred, please try again.'));
    }

    public function registerOrder(Order $order): array
    {
        if (! $order->is_paid) {
            throw new MessageException('Order must be paid.');
        }

        $payload = [
            'key' => sys_get('double_the_donation_private_key'),
            '360matchpro_public_key' => sys_get('double_the_donation_public_key'),
            'partner_identifier' => config('services.double-the-donation.partner_id'),
            'campaign' => optional($order->products()->first())->name,
            'donation_identifier' => $order->id,
            'doublethedonation_company_id' => $order->doublethedonation_company_id,
            'doublethedonation_entered_text' => $order->doublethedonation_entered_text,
            'anonymous' => $order->hasMember(),
            'donation_datetime' => $order->confirmationdatetime->toIso8601String(),
            'donor_first_name' => optional($order->member)->first_name,
            'donor_last_name' => optional($order->member)->last_name,
            'donor_phone' => optional($order->member)->bill_phone,
            'donor_email' => optional($order->member)->email,
            'donor_address' => [
                'address1' => optional($order->member)->bill_address_01 ?? '',
                'address2' => optional($order->member)->bill_address_02 ?? '',
                'city' => optional($order->member)->bill_city ?? '',
                'state' => optional($order->member)->bill_state ?? '',
                'zip' => optional($order->member)->bill_zip ?? '',
                'country' => optional($order->member)->bill_country ?? '',
            ],
            'recurring' => $order->recurring_items_count > 0,
            'donation_amount' => $order->subtotal,

            'in_memoriam' => $order->istribute,
        ];

        $response = Http::asJson()->post(static::API_URL . 'register_donation', $payload);

        if ($response->clientError() || $response->serverError()) {
            throw new MessageException((string) $response);
        }

        if (data_get($response->json(), 'duplicate?') === true) {
            return $this->getDonationRecord($order->id);
        }

        $order->doublethedonation_registered = true;
        $order->save();

        return $response->json();
    }

    public function getDonationRecord(int $id): ?array
    {
        $response = Http::asJson()->get(static::API_URL . 'get_donation', [
            'key' => sys_get('double_the_donation_private_key'),
            'donation_identifier' => $id,
        ])->json();

        return $response;
    }
}
