<?php

namespace Ds\Services;

use Ds\Models\Member;
use Ds\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class SupporterSearchService
{
    protected static $term;

    public function handle(string $term): array
    {
        static::$term = $term;

        $members = Member::query()
            ->active()
            ->where(function (Builder $query) {
                $query->where('display_name', 'LIKE', '%' . static::$term . '%')
                    ->orWhere('email', 'LIKE', '%' . static::$term . '%')
                    ->orWhere('bill_phone', 'LIKE', '%' . static::$term . '%')
                    ->orWhere('bill_phone_e164', 'LIKE', '%' . static::$term . '%')
                    ->orWhere('ship_phone', 'LIKE', '%' . static::$term . '%')
                    ->orWhere('bill_email', 'LIKE', '%' . static::$term . '%')
                    ->orWhere('ship_email', 'LIKE', '%' . static::$term . '%');
            })->limit(10)
            ->get()
            ->map(function (Member $member) {
                return $this->mapAndHighlightSupporterAttributes($member);
            });

        if ($members->isNotEmpty()) {
            return [
                'supporters' => true,
                'term' => static::$term,
                'results' => $members,
            ];
        }

        $contributions = Order::query()
            ->paid()
            ->where(function (Builder $query) {
                $query->where('billing_first_name', 'LIKE', '%' . static::$term . '%')
                    ->orWhere('billing_last_name', 'LIKE', '%' . static::$term . '%')
                    ->orWhere(DB::raw('CONCAT(billing_first_name, " ", billing_last_name)'), 'LIKE', '%' . static::$term . '%')
                    ->orwhere('shipping_first_name', 'LIKE', '%' . static::$term . '%')
                    ->orWhere('shipping_last_name', 'LIKE', '%' . static::$term . '%')
                    ->orWhere(DB::raw('CONCAT(shipping_first_name, " ", shipping_last_name)'), 'LIKE', '%' . static::$term . '%')
                    ->orWhere('billingemail', 'LIKE', '%' . static::$term . '%')
                    ->orWhere('shipemail', 'LIKE', '%' . static::$term . '%')
                    ->orWhere('billingaddress1', 'LIKE', '%' . static::$term . '%')
                    ->orWhere('shipaddress1', 'LIKE', '%' . static::$term . '%')
                    ->orWhere('billingphone', 'LIKE', '%' . static::$term . '%')
                    ->orWhere('shipphone', 'LIKE', '%' . static::$term . '%');
            })->limit(10)
            ->get()
            ->map(function (Order $order) {
                return $this->mapAndHighlightContributionAttributes($order);
            });

        return [
            'supporters' => false,
            'term' => $term,
            'results' => $contributions,
        ];
    }

    protected function mapAndHighlightSupporterAttributes(Member $member): array
    {
        $highlightedAttributes = $this->hightlightAttributes([
            'name' => $member->display_name,
            'email' => $this->getFirstMatchedAttribute([
                $member->email,
                $member->bill_email,
                $member->ship_email,
            ]),
            'phone' => $this->getFirstMatchedAttribute([
                $member->bill_phone,
                $member->bill_phone_e164,
                $member->ship_phone,
            ]),
            'address_line_1' => $this->getFirstMatchedAttribute([
                $member->bill_address_01,
                $member->ship_address_01,
            ]),
            'address_line_2' => $this->getFirstMatchedAttribute([
                $member->bill_address_02,
                $member->bill_address_02,
            ]),
        ]);

        return array_merge($highlightedAttributes, [
            'id' => $member->id,
            'city' => e($member->bill_city ?? $member->ship_city),
            'state' => e($member->bill_state ?? $member->ship_state),
            'zip' => e($member->bill_zip ?? $member->ship_zip),
            'country' => e($member->bill_country ?? $member->ship_country),
            'url' => route('backend.member.edit', $member),
        ]);
    }

    protected function mapAndHighlightContributionAttributes(Order $order): array
    {
        $highlightedAttributes = $this->hightlightAttributes([
            'name' => $this->getFirstMatchedAttribute([
                $order->billing_first_name . ' ' . $order->billing_last_name,
                $order->shipping_first_name . ' ' . $order->shipping_last_name,
            ]),

            'email' => $this->getFirstMatchedAttribute([
                $order->billingemail,
                $order->shipemail,
            ]),

            'phone' => $this->getFirstMatchedAttribute([
                $order->billingphone,
                $order->shipphone,
            ]),
            'address_line_1' => $this->getFirstMatchedAttribute([
                $order->billingaddress1,
                $order->shipaddress1,
            ]),
            'address_line_2' => $this->getFirstMatchedAttribute([
                $order->billingaddress2,
                $order->shipaddress2,
            ]),
        ]);

        return array_merge($highlightedAttributes, [
            'id' => $order->id,
            'invoicenumber' => $order->invoicenumber,
            'date' => toLocalFormat($order->confirmationdatetime),
            'city' => e($order->billingcity ?? $order->shipcity),
            'state' => e($order->billingstate ?? $order->shipstate),
            'zip' => e($order->billingzip ?? $order->shipzip),
            'country' => e($order->billingcountry ?? $order->shipcountry),
            'url' => route('backend.orders.edit', $order),
        ]);
    }

    protected function getFirstMatchedAttribute(array $attributes): ?string
    {
        $term = preg_quote(static::$term, '/');

        return array_first($attributes, function ($value) use ($term) {
            return (bool) preg_match("/($term)/i", $value);
        }) ?? $attributes[0];
    }

    protected function hightlightAttributes(array $attributes): array
    {
        return array_map(function ($value) {
            return $this->hightlightSearchTerm($value);
        }, $attributes);
    }

    private function hightlightSearchTerm($text): string
    {
        $term = preg_quote(static::$term, '/');

        return preg_replace("/($term)/i", '<mark>$1</mark>', e($text));
    }
}
