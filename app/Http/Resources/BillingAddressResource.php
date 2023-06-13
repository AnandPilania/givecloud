<?php

namespace Ds\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \Ds\Models\Order */
class BillingAddressResource extends JsonResource
{
    /**
     * Transform the rethis into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'title' => $this->billing_title ?: null,
            'name' => $this->billing_display_name ?: null,
            'first_name' => $this->billing_first_name ?: null,
            'last_name' => $this->billing_last_name ?: null,
            'email' => $this->billingemail ?: null,
            'address1' => $this->billingaddress1 ?: null,
            'address2' => $this->billingaddress2 ?: null,
            'company' => $this->billing_organization_name ?: null,
            'city' => $this->billingcity ?: null,
            'province_code' => $this->billingstate ?: null,
            'zip' => $this->billingzip ?: null,
            'country_code' => $this->billingcountry ?: null,
            'phone' => $this->billingphone ?: null,
        ];
    }
}
