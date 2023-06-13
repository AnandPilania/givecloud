<?php

namespace Ds\Http\Controllers\Frontend\API;

use Carbon\Carbon;
use Ds\Domain\Analytics\AnalyticsService;
use Ds\Domain\Analytics\UserAgent;
use Ds\Domain\Commerce\AuthorizationRateMonitor;
use Ds\Domain\Commerce\Enums\ContributionPaymentType;
use Ds\Domain\Commerce\Enums\CredentialOnFileInitiatedBy;
use Ds\Domain\Commerce\Exceptions\RedirectException;
use Ds\Domain\Commerce\Exceptions\TransactionException;
use Ds\Domain\Commerce\Models\PaymentProvider;
use Ds\Domain\Commerce\Responses\TransactionResponse;
use Ds\Domain\Commerce\SourceTokenChargeOptions;
use Ds\Domain\Commerce\SourceTokenCreateOptions;
use Ds\Domain\Commerce\SourceTokenUrlOptions;
use Ds\Domain\DoubleTheDonation\Jobs\RegisterDonation;
use Ds\Domain\MissionControl\MissionControlService;
use Ds\Domain\Shared\Exceptions\DisclosableException;
use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Domain\Theming\Liquid\Drop;
use Ds\Enums\MemberOptinSource;
use Ds\Models\Order;
use Ds\Models\OrderItem;
use Ds\Models\PaymentMethod;
use Ds\Models\RecurringPaymentProfile;
use Ds\Services\MemberService;
use Ds\Services\Order\OrderAddItemRequestValidationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Throwable;

class CheckoutsController extends Controller
{
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function oneClickCheckout(Order $cart = null)
    {
        if (request('cart_id')) {
            $cart = Order::where('client_uuid', request('cart_id'))->firstOrFail();

            $cart->requireCart();
            $cart->currency_code = request('currency_code', $cart->currency_code);

            foreach ($cart->items as $item) {
                $cart->removeItem($item->id);
            }
        } else {
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
            $cart->currency_code = request('currency_code', sys_get('dpo_currency'));
            $cart->language = request()->getPreferredLanguage();
            $cart->timezone = app('geoip')->get('timezone', request()->ip());
            $cart->tax_receipt_type = sys_get('tax_receipt_type');
            $cart->save();

            if ($member = member()) {
                $cart->populateMember($member);
            }
        }

        try {
            $items = request('items', []);

            if (request('item')) {
                $items[] = request('item');
            }

            foreach ($items as $item) {
                (new CartsController)->addProduct($cart, (array) $item);
            }

            $shouldValidate = ! in_array(request('payment_type'), ['payment_method', 'wallet_pay'], true);

            if (request('payment_type') === 'paypal' && $cart->isForFundraisingForm()) {
                $shouldValidate = false;
            }

            $this->updateCheckout($cart, $shouldValidate);

            if (request('password')) {
                (new CartsController)->createAccount($cart);
            }
        } catch (Throwable $exception) {
            return $this->failure([
                'cart' => $cart->exists ? Drop::factory($cart, 'Checkout') : null,
                'error' => $exception->getMessage(),
            ]);
        }

        return $this->success([
            'cart' => Drop::factory($cart, 'Checkout'),
        ]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCheckout(Order $cart, $requireValidation = true)
    {
        $cart->requireCart();

        $shippingMethod = request('shipping_method');

        if ($shippingMethod) {
            if (sys_get('shipping_handler') === 'tiered') {
                $cart->shipping_method_id = $shippingMethod;
                $cart->courier_method = null;
            } elseif (sys_get('shipping_handler') === 'courier') {
                $cart->shipping_method_id = null;
                $cart->courier_method = $shippingMethod;
            }

            $cart->save();
            $cart->calculate();
        }

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
            'dcc_enabled_by_customer' => sys_get('bool:dcc_ai_is_enabled')
                ? (bool) (request()->has('cover_costs_type') ? request('cover_costs_type') : $cart->dcc_type)
                : request('cover_costs_enabled', $cart->dcc_enabled_by_customer ?: false),
            'dcc_per_order_amount' => request('cover_costs_flat_fee', $cart->dcc_per_order_amount),
            'dcc_rate' => request('cover_costs_rate', $cart->dcc_rate),
            'dcc_type' => request()->has('cover_costs_type') ? request('cover_costs_type') : $cart->dcc_type,
        ];

        if ($data['ship_to_billing']) {
            $data['shipping_title'] = $data['billing_title'];
            $data['shipping_first_name'] = $data['billing_first_name'];
            $data['shipping_last_name'] = $data['billing_last_name'];
            $data['shipping_organization_name'] = $data['billing_organization_name'];
            $data['shipemail'] = $data['billingemail'];
            $data['shipaddress1'] = $data['billingaddress1'];
            $data['shipaddress2'] = $data['billingaddress2'];
            $data['shipcity'] = $data['billingcity'];
            $data['shipstate'] = $data['billingstate'];
            $data['shipzip'] = $data['billingzip'];
            $data['shipcountry'] = $data['billingcountry'];
            $data['shipphone'] = $data['billingphone'];
        }

        $rules = [
            'ship_to_billing' => 'boolean',
            'account_type_id' => 'required|integer',
            'billing_first_name' => 'required',
            'billing_last_name' => 'required',
            'billingemail' => 'required',
        ];

        if ($cart->fundraisingForm->require_billing_address ?? false) {
            $rules = array_merge($rules, [
                'billingaddress1' => 'required',
                'billingcity' => 'required',
                'billingstate' => 'required_unless:billingcountry,IL,KY',
                'billingzip' => 'required',
                'billingcountry' => 'required',
            ]);
        }

        if ($cart->shippable_items > 0 && ! $cart->ship_to_billing) {
            $rules = array_merge($rules, [
                'shipping_first_name' => 'required',
                'shipping_last_name' => 'required',
                'shipaddress1' => 'required',
                'shipcity' => 'required',
                'shipstate' => 'required_unless:shipcountry,IL,KY',
                'shipzip' => 'required',
                'shipcountry' => 'required',
            ]);
        }

        $validator = app('validator')->make($data, $rules);
        $validator->setAttributeNames([
            'account_type_id' => 'supporter type',
            'billing_first_name' => 'billing first name',
            'billing_last_name' => 'billing last name',
            'billingemail' => 'billing email',
            'billingaddress1' => 'billing address 1',
            'billingcity' => 'billing city',
            'billingstate' => 'billing province',
            'billingzip' => 'billing zip',
            'billingcountry' => 'billing country',
            'billingphone' => 'billing phone',
            'shipping_first_name' => 'shipping first name',
            'shipping_last_name' => 'shipping last name',
            'shipaddress1' => 'shipping address 1',
            'shipcity' => 'shipping city',
            'shipstate' => 'shipping province',
            'shipzip' => 'shipping zip',
            'shipcountry' => 'shipping country',
            'referral_source' => 'referral source',
        ]);

        if ($requireValidation && $validator->fails()) {
            throw new MessageException($validator->errors()->first());
        }

        $cart->fill($data);
        $cart->save();
        $cart->revalidatePerAccountPromos();
        $cart->calculate();

        return $this->success([
            'cart' => Drop::factory($cart, 'Checkout'),
        ]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateDcc(Order $cart)
    {
        $cart->requireCart();

        $cart->dcc_enabled_by_customer = request('cover_costs_enabled', $cart->dcc_enabled_by_customer ?: false);
        $cart->dcc_type = request('cover_costs_type');
        $cart->save();

        $cart->calculate();

        return $this->success([
            'cart' => Drop::factory($cart, 'Checkout'),
        ]);
    }

    public function updateOptIn(Order $cart): JsonResponse
    {
        $cart->requireRecentCart();

        $cart->email_opt_in = request('email_opt_in', $cart->email_opt_in ?: false);
        $cart->save();

        if ($cart->member) {
            $optin = request('source') === 'checkout_nag'
                ? MemberOptinSource::CHECKOUT_NAG
                : MemberOptinSource::CHECKOUT;

            app(MemberService::class)->setMember($cart->member)->optin($optin);
        }

        return $this->success([
            'cart' => Drop::factory($cart, 'Checkout'),
        ]);
    }

    public function upgradeItem(Order $cart, OrderItem $item): JsonResponse
    {
        $cart->requireRecentCart();

        if ($cart->id !== $item->productorderid) {
            return $this->failure(__('frontend/api.item_from_another_cart'), 403);
        }

        if (! $cart->isForFundraisingForm()) {
            return $this->failure(__('frontend/api.unable_to_upgrade_item'), 422);
        }

        $data = app(OrderAddItemRequestValidationService::class)->validate([
            'variant_id' => request('variant_id'),
            'amt' => request('amt'),
            'recurring_frequency' => request('recurring_frequency'),
        ]);

        $recurringItem = $item->replicate();
        $recurringItem->productinventoryid = $data->variant_id;
        $recurringItem->original_variant_id = $data->variant_id;
        $recurringItem->price = 0;
        $recurringItem->original_price = 0;
        $recurringItem->recurring_amount = $data->amt;
        $recurringItem->recurring_frequency = $data->recurring_frequency;
        $recurringItem->dcc_amount = 0;
        $recurringItem->dcc_recurring_amount = (float) request('dcc');
        $recurringItem->locked_to_item_id = $item->getKey();
        $recurringItem->save();

        $item->upgraded_to_recurring = true;
        $item->save();

        $cart->loadLoaded('items');
        $cart->updateAggregates();
        $cart->save();

        $rpp = RecurringPaymentProfile::createUsingOrderItemAndCartAndPaymentMethod($recurringItem, $cart, $cart->paymentMethod);
        $rpp->save();

        return $this->success([
            'cart' => Drop::factory($cart, 'Checkout'),
        ]);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function captureToken(Order $cart)
    {
        $cart->requireCart();

        $cart->payment_type = request('payment_type');
        $cart->payment_method_id = null;

        if ($cart->payment_type === 'credit_card') {
            if ($cart->source === 'Kiosk') {
                $provider = PaymentProvider::getKioskProvider();
            } else {
                $provider = PaymentProvider::getCreditCardProvider();
            }
        } elseif ($cart->payment_type === 'bank_account') {
            $provider = PaymentProvider::getBankAccountProvider();
        } elseif ($cart->payment_type === 'paypal') {
            $provider = PaymentProvider::getPayPalProvider();
        } elseif ($cart->payment_type === 'payment_method') {
            if ($cart->member) {
                $paymentMethod = $cart->member->paymentMethods()
                    ->where('id', request('payment_method'))
                    ->active()
                    ->first();
                if ($paymentMethod) {
                    $cart->payment_method_id = $paymentMethod->id;
                    $cart->billing_first_name = $cart->billing_first_name ?: $paymentMethod->billing_first_name;
                    $cart->billing_last_name = $cart->billing_last_name ?: $paymentMethod->billing_last_name;
                    $cart->billingemail = $cart->billingemail ?: $paymentMethod->billing_email;
                    $cart->billingaddress1 = $cart->billingaddress1 ?: $paymentMethod->billing_address1;
                    $cart->billingaddress2 = $cart->billingaddress2 ?: $paymentMethod->billing_address2;
                    $cart->billingcity = $cart->billingcity ?: $paymentMethod->billing_city;
                    $cart->billingstate = $cart->billingstate ?: $paymentMethod->billing_state;
                    $cart->billingzip = $cart->billingzip ?: $paymentMethod->billing_postal;
                    $cart->billingcountry = $cart->billingcountry ?: $paymentMethod->billing_country;
                    $cart->billingphone = $cart->billingphone ?: $paymentMethod->billing_phone;
                    $provider = $paymentMethod->paymentProvider;
                } else {
                    throw new MessageException(__('frontend/api.invalid_payment_method'));
                }
            } else {
                throw new MessageException(__('frontend/api.not_logged_in'));
            }
        } elseif ($cart->payment_type === ContributionPaymentType::WALLET_PAY) {
            $provider = PaymentProvider::getWalletPayProvider();
        } else {
            throw new MessageException(__('frontend/api.no_payment_method'));
        }

        if (sys_get('allow_overriding_payment_providers') && $cart->payment_type !== 'payment_method' && $provider && $provider->provider !== request('provider')) {
            $provider = PaymentProvider::enabled()
                ->provider(request('provider'))
                ->orderBy('provider', 'asc')
                ->first();
        }

        if (! $provider) {
            throw new MessageException(__('frontend/api.no_payment_gateway'));
        }

        $cart->payment_provider_id = $provider->id;
        $cart->save();

        $cart->calculate();

        // must check again after setting `payment_type` since the stop
        // payments check now relies on this to block only credit card payments
        $this->validateCartForPaymentProcessing($cart);

        try {
            $validator = new \Ds\OrderValidator($cart);

            $validator->validatePresenceOfItems();
            $validator->validatePresenceOfBillingInformation();
            $validator->validatePresenceOfOrganizationName();
            $validator->validatePresenceOfMember();
            $validator->validateMinimumOrderTotal();
            $validator->validateShippingRequirements();
            $validator->validatePerAccountPromos();
            $validator->validateItemAvailability();
            $validator->validateSalesLimits();
            $validator->validateAchRequirements();
        } catch (MessageException $exception) {
            return $this->failure($exception);
        }

        if ($cart->requiresCaptcha()) {
            if (app('recaptcha')->verify()) {
                $cart->captcha = 'pass';
                $cart->save();
            } else {
                $cart->captcha = 'fail';
                $cart->save();

                return $this->failure([
                    'error' => __('general.captcha.validation_failed'),
                    'captcha' => true,
                ]);
            }
        }

        if ($cart->paymentMethod) {
            return $this->success([
                'token_id' => $cart->paymentMethod->token,
            ]);
        }

        $savePaymentMethod = $cart->recurring_items || request('save_payment_method');

        $returnUrl = secure_site_url("/carts/{$cart->client_uuid}/tokenize/return");
        $cancelUrl = secure_site_url("/carts/{$cart->client_uuid}/tokenize/cancel");

        if ($context = request('context')) {
            $returnUrl .= '?rt_context=' . urlencode($context);
            $cancelUrl .= '?rt_context=' . urlencode($context);
        }

        if ($cart->paymentProvider->supports('capture_tokens') && ! $savePaymentMethod) {
            return $cart->paymentProvider->getCaptureTokenUrl($cart, $returnUrl, $cancelUrl);
        }

        if ($cart->paymentProvider->supports('source_tokens')) {
            if (! $cart->member_id && ! ($cart->payment_type === ContributionPaymentType::PAYPAL && $cart->isForFundraisingForm())) {
                $cart->createMember();
            }

            $paymentMethod = new PaymentMethod;
            $paymentMethod->member_id = $cart->member_id;
            $paymentMethod->payment_provider_id = $cart->payment_provider_id;
            $paymentMethod->status = 'PENDING';
            $paymentMethod->currency_code = $cart->currency_code;
            $paymentMethod->billing_first_name = $cart->billing_first_name;
            $paymentMethod->billing_last_name = $cart->billing_last_name;
            $paymentMethod->billing_email = $cart->billingemail;
            $paymentMethod->billing_address1 = $cart->billingaddress1;
            $paymentMethod->billing_address2 = $cart->billingaddress2;
            $paymentMethod->billing_city = $cart->billingcity;
            $paymentMethod->billing_state = $cart->billingstate;
            $paymentMethod->billing_postal = $cart->billingzip;
            $paymentMethod->billing_country = $cart->billingcountry;
            $paymentMethod->billing_phone = $cart->billingphone;
            $paymentMethod->credential_on_file = $cart->paymentProvider->usingCredentialOnFile();
            $paymentMethod->save();

            $cart->payment_method_id = $paymentMethod->id;
            $cart->save();

            return $cart->paymentProvider->getSourceTokenUrl(
                $paymentMethod,
                $returnUrl,
                $cancelUrl,
                new SourceTokenUrlOptions(['contribution' => $cart]),
            );
        }

        throw new MessageException(__('frontend/api.payment_gateway_not_configured'));
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function chargeToken(Order $cart)
    {
        $this->validateCartForPaymentProcessing($cart);

        if (! $cart->paymentProvider) {
            throw new MessageException(__('frontend/api.no_payment_provider'));
        }

        if ($cart->requiresCaptcha() && $cart->captcha !== 'pass') {
            return $this->failure([
                'error' => __('general.captcha.validation_failed'),
                'captcha' => true,
            ]);
        }

        $cart->increment('auth_attempts');

        $handleException = function (Throwable $e) use ($cart) {
            $cart->response_text = $e->getMessage();
            $cart->captcha = null;
            $cart->save();

            app('activitron')->increment('Site.payments.failure');

            if ($e instanceof RedirectException) {
                return $e->getRedirect();
            }

            $data = [
                'error' => (string) $e,
                'captcha' => $cart->requiresCaptcha(),
            ];

            if ($cart->latestPayment->failure_code ?? false) {
                $data['payment_failure'] = [
                    'error' => $cart->response_text,
                    'friendly_message' => trans('payments.payment_failure_friendly_messages')[$cart->latestPayment->failure_code] ?? trans('payments.payment_failure_friendly_messages.fallback'),
                    'corrective_action' => trans('payments.payment_failure_corrective_actions')[$cart->latestPayment->failure_code] ?? trans('payments.payment_failure_corrective_actions.fallback'),
                ];
            }

            return $this->failure($data);
        };

        try {
            if ($cart->payment_type !== 'none') {
                if ($cart->paymentMethod) {
                    $res = null;

                    if ($cart->paymentMethod->status === 'PENDING') {
                        $res = $cart->paymentProvider->createSourceToken(
                            $cart->paymentMethod,
                            new SourceTokenCreateOptions(['contribution' => $cart]),
                        );

                        $cart->paymentMethod->updateWithTransactionResponse($res);
                    }

                    if ($cart->totalamount) {
                        $res = $cart->paymentMethod->charge(
                            $cart->totalamount,
                            $cart->currency_code,
                            new SourceTokenChargeOptions([
                                'dccAmount' => $cart->dcc_total_amount,
                                'contribution' => $cart,
                                'initiatedBy' => CredentialOnFileInitiatedBy::CUSTOMER,
                            ]),
                        );
                    } else {
                        $res = TransactionResponse::fromPaymentMethod($cart->paymentMethod);
                    }

                    // ensure a dangling PM gets linked to an actual supporter the only case
                    // in which this should be occurring is PayPal on fundraising forms
                    if (! $cart->paymentMethod->member_id) {
                        $cart->createMember();

                        $cart->paymentMethod->member_id = $cart->member_id;
                        $cart->paymentMethod->save();
                    }

                    $cart->updateWithTransactionResponse($res);
                } elseif ($cart->totalamount) {
                    $res = $cart->paymentProvider->chargeCaptureToken($cart);
                    $cart->updateWithTransactionResponse($res);
                }
            }
        } catch (TransactionException $e) {
            $cart->updateWithTransactionResponse($e->getResponse());

            return $handleException($e);
        } catch (DisclosableException $e) {
            return $handleException($e);
        }

        $cart->confirmationdatetime = Carbon::now();
        $cart->createddatetime = Carbon::now();
        $cart->ordered_at = fromUtc($cart->ordered_at ?? $cart->createddatetime);
        $cart->invoicenumber = $cart->client_uuid;
        $cart->is_processed = true;
        $cart->save();

        if ($cart->isForFundraisingForm()) {
            $product = $cart->items[0]->variant->product;

            app(AnalyticsService::class)->collectEvent($product, [
                'event_category' => 'fundraising_forms',
                'event_name' => 'contribution_paid',
                'event_value' => $cart->getKey(),
            ]);
        }

        $cart->afterProcessed();

        app('activitron')->increment('Site.payments.success');

        return $this->success([
            'cart' => Drop::factory($cart, 'Checkout'),
            'success' => true,
        ]);
    }

    /**
     * Complete the cart.
     *
     * @param \Ds\Models\Order $cart
     * @return \Illuminate\Http\JsonResponse
     */
    public function completeCart(Order $cart)
    {
        $this->validateCartForPaymentProcessing($cart);

        if ($cart->totalamount > 0 || $cart->recurring_items > 0) {
            return $this->failure(__('frontend/api.cart_missing_payment'));
        }

        try {
            $validator = new \Ds\OrderValidator($cart);

            $validator->validatePresenceOfItems();
            $validator->validatePresenceOfMember();
            $validator->validateShippingRequirements();
            $validator->validateItemAvailability();
            $validator->validateSalesLimits();
        } catch (MessageException $exception) {
            return $this->failure($exception);
        }

        $cart->confirmationdatetime = Carbon::now();
        $cart->createddatetime = Carbon::now();
        $cart->ordered_at = fromUtc($cart->ordered_at ?? $cart->createddatetime);
        $cart->invoicenumber = $cart->client_uuid;
        $cart->is_processed = true;
        $cart->save();

        $cart->afterProcessed();

        return $this->success([
            'cart' => Drop::factory($cart, 'Checkout'),
        ]);
    }

    public function updateEmployerMatch(Order $cart)
    {
        $cart->requireRecentCart();

        $cart->doublethedonation_status = request('doublethedonation_status');
        $cart->doublethedonation_company_id = request('doublethedonation_company_id');
        $cart->doublethedonation_entered_text = request('doublethedonation_entered_text');
        $cart->doublethedonation_company_name = request('doublethedonation_company_name');

        $cart->save();

        RegisterDonation::dispatch($cart);

        return $this->success([
            'cart' => Drop::factory($cart, 'Checkout'),
        ]);
    }

    public function updateReferralSource(Order $cart)
    {
        $cart->requireRecentCart();

        $cart->referral_source = request('referral_source', $cart->referral_source);
        $cart->save();

        return $this->success([
            'cart' => Drop::factory($cart, 'Checkout'),
        ]);
    }

    /**
     * Validate an Order for payment processing.
     *
     * @param \Ds\Models\Order $cart
     */
    private function validateCartForPaymentProcessing(Order $cart)
    {
        $cart->requireCart();

        AuthorizationRateMonitor::check();

        $cart->validate([
            'minimum_order_total',
            'authorization_attempts',
            'billing_country_matches_ip',
        ]);

        $blockableCreditCard = $cart->payment_type === ContributionPaymentType::CREDIT_CARD;
        $blockablePaymentMethod = $cart->payment_type === ContributionPaymentType::PAYMENT_METHOD
            && $cart->paymentMethod
            && $cart->paymentMethod->cc_expiry
            && fromUtc($cart->paymentMethod->created_at)->addDays(5)->isFuture();

        if (sys_get('public_payments_disabled') && ($blockableCreditCard || $blockablePaymentMethod) && ! user('id')) {
            throw new MessageException(__('frontend/api.payment_gateway_offline'));
        }

        if (sys_get('enforce_ip_blocklists') && app(MissionControlService::class)->isBlockedIp()) {
            throw new MessageException('Unknown error (9012).');
        }

        if (UserAgent::make()->isRobot()) {
            throw new MessageException('Unknown error (9013).');
        }
    }
}
