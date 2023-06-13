<?php

namespace Ds\Http\Controllers\Frontend\API;

use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Domain\Theming\Liquid\Drop;
use Ds\Models\Order;
use Ds\Models\OrderItem;
use Ds\Models\PromoCode;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Throwable;

class CartsController extends Controller
{
    /**
     * Create a cart.
     *
     * Ability to create a cart from one of the following:
     *   - product_id
     *   - sponsorship_id
     *   - permalink (which is then resolved to product_id or sponsorship_id)
     *
     * The primary use case for this is one-page checkout, modal checkouts
     * one-click donation/purchase buttons, etc.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function createCart()
    {
        $handle = request('handle');

        $cart = new Order;
        $cart->member_id = member('id');
        $cart->client_uuid = uuid();
        $cart->client_ip = request()->ip();
        $cart->client_browser = Str::limit(request()->server('HTTP_USER_AGENT'), 2048, '') ?: null;
        $cart->http_referer = Str::limit(request()->server('HTTP_REFERER'), 512, '') ?: null;
        $cart->tracking_source = Str::limit(request('utm_source'), 50, '') ?: null;
        $cart->tracking_medium = Str::limit(request('utm_medium'), 50, '') ?: null;
        $cart->tracking_campaign = Str::limit(request('utm_campaign'), 50, '') ?: null;
        $cart->tracking_term = Str::limit(request('utm_term'), 50, '') ?: null;
        $cart->tracking_content = Str::limit(request('utm_content'), 50, '') ?: null;
        $cart->ga_client_id = request()->cookie('_ga') ?: null;
        $cart->source = 'Web';
        $cart->started_at = fromUtc('now');
        $cart->currency_code = sys_get('dpo_currency');
        $cart->language = request()->getPreferredLanguage();
        $cart->timezone = app('geoip')->get('timezone', request()->ip());
        $cart->tax_receipt_type = sys_get('tax_receipt_type');
        $cart->ship_to_billing = true;
        $cart->dcc_enabled_by_customer = (bool) sys_get('dcc_ai_is_enabled');
        $cart->dcc_type = sys_get('dcc_ai_is_enabled') ? 'more_costs' : null;
        $cart->save();

        if ($member = member()) {
            $cart->populateMember($member);
        }

        try {
            return (new CheckoutsController)->updateCheckout($cart, false);
        } catch (Throwable $e) {
            return $this->success(Drop::factory($cart, 'Checkout'));
        }
    }

    /**
     * Get the cart.
     *
     * @param \Ds\Models\Order $cart
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCart(Order $cart)
    {
        $data = Drop::factory($cart, 'Checkout')->toArray();

        return $this->success(array_merge($data, [
            'response_text' => $cart->response_text,
            'single_use_token' => $cart->single_use_token,
        ]));
    }

    /**
     * Update the cart.
     *
     * @param \Ds\Models\Order $cart
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCart(Order $cart)
    {
        $cart->requireCart();

        $data = [
            'ship_to_billing' => request('ship_to_billing', $cart->ship_to_billing ?: false),
            'account_type_id' => request('account_type_id', $cart->account_type_id),
            'billing_title' => request('billing_title', $cart->billing_title),
            'billing_first_name' => request('billing_first_name', $cart->billing_first_name),
            'billing_last_name' => request('billing_last_name', $cart->billing_last_name),
            'billing_organization_name' => request('billing_company', $cart->billing_organization_name),
            'billingemail' => request('billing_email', $cart->billingemail),
            'billingaddress1' => request('billing_address1', $cart->billingaddress1),
            'billingaddress2' => request('billing_address2', $cart->billingaddress2),
            'billingcity' => request('billing_city', $cart->billingcity),
            'billingstate' => request('billing_province_code', $cart->billingstate),
            'billingzip' => request('billing_zip', $cart->billingzip),
            'billingcountry' => request('billing_country_code', $cart->billingcountry),
            'billingphone' => request('billing_phone', $cart->billingphone),
            'email_opt_in' => request('email_opt_in', $cart->email_opt_in ?: false),
            'referral_source' => request('referral_source', $cart->referral_source),
            'shipping_title' => request('shipping_title', $cart->shipping_title),
            'shipping_first_name' => request('shipping_first_name', $cart->shipping_first_name),
            'shipping_last_name' => request('shipping_last_name', $cart->shipping_last_name),
            'shipping_organization_name' => request('shipping_company', $cart->shipping_organization_name),
            'shipemail' => request('shipping_email', $cart->shipemail),
            'shipaddress1' => request('shipping_address1', $cart->shipaddress1),
            'shipaddress2' => request('shipping_address2', $cart->shipaddress2),
            'shipcity' => request('shipping_city', $cart->shipcity),
            'shipstate' => request('shipping_province_code', $cart->shipstate),
            'shipzip' => request('shipping_zip', $cart->shipzip),
            'shipcountry' => request('shipping_country_code', $cart->shipcountry),
            'shipphone' => request('shipping_phone', $cart->shipphone),
            'is_anonymous' => request('is_anonymous', $cart->is_anonymous ?: false),
            'comments' => request('comments', $cart->comments),
            'currency_code' => request('currency_code', $cart->currency_code),
            'payment_type' => request('payment_type', $cart->payment_type),
            'dcc_enabled_by_customer' => request('cover_costs_enabled', $cart->dcc_enabled_by_customer ?: false),
            'dcc_per_order_amount' => request('cover_costs_flat_fee', $cart->dcc_per_order_amount),
            'dcc_rate' => request('cover_costs_rate', $cart->dcc_rate),
            'dcc_type' => request()->has('cover_costs_type') ? request('cover_costs_type') : $cart->dcc_type,
        ];

        if ($shippingMethod = request('shipping_method_value')) {
            if (sys_get('shipping_handler') === 'tiered') {
                $data['shipping_method_id'] = $shippingMethod;
            } else {
                $data['courier_method'] = $shippingMethod;
            }
        }

        $cart->fill($data);
        $cart->save();

        $cart->calculate();

        return $this->success([
            'cart' => Drop::factory($cart, 'Checkout'),
        ]);
    }

    /**
     * Delete the cart.
     *
     * @param \Ds\Models\Order $cart
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteCart(Order $cart)
    {
        return $this->failure();
    }

    /**
     * Get the items in the cart.
     *
     * @param \Ds\Models\Order $cart
     * @return \Illuminate\Http\JsonResponse
     */
    public function getItems(Order $cart)
    {
        return $this->success([
            'items' => $cart->items,
        ]);
    }

    /**
     * Empty the items from the cart.
     *
     * @param \Ds\Models\Order $cart
     * @return \Illuminate\Http\JsonResponse
     */
    public function emptyCart(Order $cart)
    {
        $cart->requireCart();

        foreach ($cart->items as $item) {
            $cart->removeItem($item->id);
        }

        return $this->success([
            'cart' => Drop::factory($cart, 'Checkout'),
        ]);
    }

    /**
     * Add item to the cart.
     *
     * @param \Ds\Models\Order $cart
     * @return \Illuminate\Http\JsonResponse
     */
    public function addItem(Order $cart)
    {
        $cart->requireCart();

        switch (request('type')) {
            case 'discount_item':
                $this->addDiscount($cart, (array) request('data'));
                break;
            case 'product_item':
                $this->addProduct($cart, (array) request('data'));
                break;
            case 'sponsorship_item':
                $this->addSponsorship($cart, (array) request('data'));
                break;
            default:
                return $this->failure(__('frontend/api.unsupported_type'));
        }

        return $this->success([
            'cart' => Drop::factory($cart, 'Checkout'),
        ]);
    }

    /**
     * Add a discount to the cart.
     *
     * @param \Ds\Models\Order $cart
     * @param array $data
     */
    private function addDiscount(Order $cart, array $data)
    {
        $cart->requireCart();

        $code = strtoupper((string) Arr::get($data, 'code'));

        PromoCode::validate($code, $cart->is_pos, $cart->billingemail, $cart->member);

        $codes = $cart->applyPromos($code);

        if (empty($codes)) {
            throw new MessageException(__('frontend/api.code_could_not_be_applied'));
        }
    }

    /**
     * Add a product to the cart.
     *
     * @param \Ds\Models\Order $cart
     * @param array $data
     */
    public function addProduct(Order $cart, array $data)
    {
        $cart->requireCart();

        $cart->addItem([
            'variant_id' => Arr::get($data, 'variant_id'),
            'amt' => Arr::get($data, 'amt'),
            'qty' => Arr::get($data, 'quantity'),
            'recurring_frequency' => Arr::get($data, 'recurring_frequency'),
            'recurring_day' => Arr::get($data, 'recurring_day'),
            'recurring_day_of_week' => Arr::get($data, 'recurring_day_of_week'),
            'recurring_with_initial_charge' => Arr::get($data, 'recurring_with_initial_charge'),
            'recurring_with_dpo' => Arr::get($data, 'recurring_with_dpo'),
            'is_tribute' => Arr::get($data, 'is_tribute'),
            'dpo_tribute_id' => Arr::get($data, 'dpo_tribute_id'),
            'tribute_name' => Arr::get($data, 'tribute_name'),
            'tribute_type_id' => Arr::get($data, 'tribute_type_id'),
            'tribute_notify' => Arr::get($data, 'tribute_notify'),
            'tribute_notify_at' => Arr::get($data, 'tribute_notify_at'),
            'tribute_message' => Arr::get($data, 'tribute_message'),
            'tribute_notify_name' => Arr::get($data, 'tribute_notify_name'),
            'tribute_notify_email' => Arr::get($data, 'tribute_notify_email'),
            'tribute_notify_address' => Arr::get($data, 'tribute_notify_address'),
            'tribute_notify_city' => Arr::get($data, 'tribute_notify_city'),
            'tribute_notify_state' => Arr::get($data, 'tribute_notify_state'),
            'tribute_notify_zip' => Arr::get($data, 'tribute_notify_zip'),
            'tribute_notify_country' => Arr::get($data, 'tribute_notify_country'),
            'public_message' => Arr::get($data, 'public_message'),
            'fields' => Arr::get($data, 'form_fields'),
            'gl_code' => Arr::get($data, 'gl_code'),
            'fundraising_page_id' => Arr::get($data, 'fundraising_page_id'),
            'fundraising_member_id' => Arr::get($data, 'fundraising_member_id'),
            'gift_aid' => Arr::get($data, 'gift_aid'),
            'metadata' => Arr::get($data, 'metadata'),
        ]);
    }

    /**
     * Add a sponsorship to the cart.
     *
     * @param \Ds\Models\Order $cart
     * @param array $data
     */
    private function addSponsorship(Order $cart, array $data)
    {
        $cart->addSponsorship([
            'sponsorship_id' => Arr::get($data, 'sponsorship_id'),
            'payment_option_id' => Arr::get($data, 'payment_option_id'),
            'payment_option_amount' => Arr::get($data, 'payment_option_amount'),
            'initial_charge' => (bool) Arr::get($data, 'initial_charge'),
        ]);
    }

    /**
     * Update item in the cart.
     *
     * @param \Ds\Models\Order $cart
     * @param \Ds\Models\OrderItem $item
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateItem(Order $cart, OrderItem $item)
    {
        $cart->requireCart();

        if ($cart->id !== $item->productorderid) {
            return $this->failure(__('frontend/api.item_from_another_cart'), 403);
        }

        $quantity = request('quantity');

        // Update the quantity first incase the item is
        // reduced to 0 and the item needs to be removed from the cart
        if ($quantity !== null && (int) $quantity !== $item->qty) {
            if ($quantity < 1) {
                $cart->removeItem($item->id);

                // if the item is remove there's no need to
                // update anything else on the item
                return $this->success([
                    'cart' => Drop::factory($cart->fresh(), 'Checkout'),
                ]);
            }

            if ($quantity > 1 && $item->is_donation) {
                return $this->failure(__('frontend/api.cannot_have_more_than_one_donation_item'), 422);
            }

            $item->setQuantity($quantity);
        }

        $amount = request('amt');

        if ($amount && $item->is_donation) {
            $item->setAmount($amount);
        }

        // TODO: update fields

        return $this->success([
            'cart' => Drop::factory($cart->fresh(), 'Checkout'),
        ]);
    }

    /**
     * Remove item from the cart.
     *
     * @param \Ds\Models\Order $cart
     * @param \Ds\Models\OrderItem $item
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeItem(Order $cart, OrderItem $item)
    {
        $cart->requireCart();

        if ($cart->id !== $item->productorderid) {
            return $this->failure(__('frontend/api.item_from_another_cart'), 403);
        }

        $cart->removeItem($item->id);

        return $this->success([
            'cart' => Drop::factory($cart->fresh(), 'Checkout'),
        ]);
    }

    /**
     * Create an account from the cart.
     *
     * @param \Ds\Models\Order $cart
     * @return \Illuminate\Http\JsonResponse
     */
    public function createAccount(Order $cart)
    {
        try {
            member_create_from_order($cart->client_uuid, request('password'), true);
        } catch (Throwable $e) {
            // do nothing
        }

        if ($member = member()) {
            $cart->member_id = $member->id;
            $cart->save();

            $cart->load('member');

            return $this->success([
                'account' => Drop::factory($member, 'Account'),
            ]);
        }

        return $this->failure(__('frontend/api.email_already_in_use'), 422);
    }
}
