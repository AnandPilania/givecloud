<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drop;
use Illuminate\Database\Eloquent\Model;

class AddressDrop extends Drop
{
    /**
     * Create an instance.
     *
     * @param \Illuminate\Database\Eloquent\Model|null $source
     * @param string $type
     */
    public function __construct(Model $source = null, $type = 'billing')
    {
        $this->source = $source;

        $this->initialize($source, $type);

        foreach ($this->attributes as $key) {
            $this->liquid[$key] = $source->getAttribute($key);
        }
    }

    protected function initialize($source, $type = 'billing')
    {
        if (is_instanceof($source, 'Ds\Models\Order')) {
            if ($type === 'billing') {
                $this->liquid = [
                    'title' => $source->billing_title ?: null,
                    'name' => $source->billing_display_name ?: null,
                    'first_name' => $source->billing_first_name ?: null,
                    'last_name' => $source->billing_last_name ?: null,
                    'email' => $source->billingemail ?: null,
                    'address1' => $source->billingaddress1 ?: null,
                    'address2' => $source->billingaddress2 ?: null,
                    'company' => $source->billing_organization_name ?: null,
                    'city' => $source->billingcity ?: null,
                    'province_code' => $source->billingstate ?: null,
                    'zip' => $source->billingzip ?: null,
                    'country_code' => $source->billingcountry ?: null,
                    'phone' => $source->billingphone ?: null,
                ];
            }

            if ($type === 'shipping') {
                $this->liquid = [
                    'title' => $source->shipping_title ?: null,
                    'name' => $source->shipping_display_name ?: null,
                    'first_name' => $source->shipping_first_name ?: null,
                    'last_name' => $source->shipping_last_name ?: null,
                    'email' => $source->shipemail ?: null,
                    'address1' => $source->shipaddress1 ?: null,
                    'address2' => $source->shipaddress2 ?: null,
                    'company' => $source->shipping_organization_name ?: null,
                    'city' => $source->shipcity ?: null,
                    'province_code' => $source->shipstate ?: null,
                    'zip' => $source->shipzip ?: null,
                    'country_code' => $source->shipcountry ?: null,
                    'phone' => $source->shipphone ?: null,
                ];
            }
        } elseif (is_instanceof($source, 'Ds\Models\PaymentMethod')) {
            $this->liquid = [
                'title' => null,
                'name' => null,
                'first_name' => $source->billing_first_name ?: null,
                'last_name' => $source->billing_last_name ?: null,
                'email' => $source->billing_email ?: null,
                'address1' => $source->billing_address1 ?: null,
                'address2' => $source->billing_address2 ?: null,
                'company' => $source->billing_ping_organization_name ?: null,
                'city' => $source->billing_city ?: null,
                'province_code' => $source->billing_state ?: null,
                'zip' => $source->billing_postal ?: null,
                'country_code' => $source->billing_country ?: null,
                'phone' => $source->billing_phone ?: null,
            ];
        } elseif (is_instanceof($source, 'Ds\Models\Member')) {
            if ($type === 'billing') {
                $this->liquid = [
                    'title' => $source->bill_title ?: null,
                    'name' => trim("$source->bill_first_name $source->bill_last_name") ?: null,
                    'first_name' => $source->bill_first_name ?: null,
                    'last_name' => $source->bill_last_name ?: null,
                    'email' => $source->bill_email ?: null,
                    'address1' => $source->bill_address_01 ?: null,
                    'address2' => $source->bill_address_02 ?: null,
                    'company' => $source->bill_organization_name ?: null,
                    'city' => $source->bill_city ?: null,
                    'province_code' => $source->bill_state ?: null,
                    'zip' => $source->bill_zip ?: null,
                    'country_code' => $source->bill_country ?: null,
                    'phone' => $source->bill_phone ?: null,
                ];
            }

            if ($type === 'shipping') {
                $this->liquid = [
                    'title' => $source->ship_title ?: null,
                    'name' => trim("$source->ship_first_name $source->ship_last_name") ?: null,
                    'first_name' => $source->ship_first_name ?: null,
                    'last_name' => $source->ship_last_name ?: null,
                    'email' => $source->ship_email ?: null,
                    'address1' => $source->ship_address_01 ?: null,
                    'address2' => $source->ship_address_02 ?: null,
                    'company' => $source->ship_organization_name ?: null,
                    'city' => $source->ship_city ?: null,
                    'province_code' => $source->ship_state ?: null,
                    'zip' => $source->ship_zip ?: null,
                    'country_code' => $source->ship_country ?: null,
                    'phone' => $source->ship_phone ?: null,
                ];
            }
        }
    }

    public function country()
    {
        return app('iso3166')->country($this->liquid['country_code'], 'name');
    }

    public function province()
    {
        return app('iso3166')->subdivision($this->liquid['country_code'] . '-' . $this->liquid['province_code'], 'name');
    }

    public function street()
    {
        return trim($this->liquid['address1'] . ' ' . $this->liquid['address2']);
    }

    public function ispopulated()
    {
        return ! $this->isempty();
    }

    public function isempty()
    {
        return implode('', $this->liquid) === '';
    }
}
