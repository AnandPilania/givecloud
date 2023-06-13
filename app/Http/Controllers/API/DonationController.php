<?php

namespace Ds\Http\Controllers\API;

use Carbon\Carbon;
use Ds\Domain\Kiosk\Models\Kiosk;
use Ds\Domain\Theming\Liquid\Drop;
use Ds\Events\OrderWasCompleted;
use Ds\Listeners\Order\IssueTaxReciept;
use Ds\Models\Order;
use Ds\Models\PaymentMethod;
use Throwable;

class DonationController extends Controller
{
    /**
     * Register controller middleware.
     */
    protected function registerMiddleware()
    {
        $this->middleware('auth', ['except' => [
            'showNetworkMerchantsToken',
        ]]);
    }

    /**
     * Create an Order with minimal information and begin a 3-step transaction.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function startDonation()
    {
        $order = new Order;
        $order->client_uuid = uuid();
        $order->client_ip = request()->ip();
        $order->client_browser = request()->server('HTTP_USER_AGENT');
        $order->started_at = Carbon::now();
        $order->currency_code = request('currency_code', sys_get('dpo_currency'));
        $order->language = request()->getPreferredLanguage();
        $order->timezone = app('geoip')->get('timezone', request()->ip());
        $order->payment_type = 'credit_card';
        $order->source = 'Kiosk';
        $order->source_id = request('kiosk_id');
        $order->tax_receipt_type = sys_get('tax_receipt_type');

        if ($order->source_id) {
            $kiosk = Kiosk::findOrFail($order->source_id);
            $order->tracking_source = $kiosk->config('tracking.source');
            $order->tracking_medium = $kiosk->config('tracking.medium');
            $order->tracking_campaign = $kiosk->config('tracking.campaign');
            $order->tracking_term = $kiosk->config('tracking.term');
            $order->tracking_content = $kiosk->config('tracking.content');
            $order->referral_source = $kiosk->config('tracking.referral_source');
        }

        $order->addItem([
            'variant_id' => request('variant_id'),
            'amt' => request('amount'),
            'qty' => 1,
            'recurring_frequency' => request('recurring_frequency'),
            'recurring_day' => request('recurring_day'),
            'recurring_day_of_week' => request('recurring_day_of_week'),
            'recurring_with_initial_charge' => request('recurring_with_initial_charge'),
            'recurring_with_dpo' => request('recurring_with_dpo'),
            'fields' => request('fields'),
            'gl_code' => request('gl_code'),
        ]);

        $this->updateCart($order);

        return response()->json(Drop::factory($order, 'Checkout'));
    }

    /**
     * Start a 3-step transaction.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function startPayment(Order $order)
    {
        return response()->json(Drop::factory($order, 'Checkout'));
    }

    /**
     * Show the token sent with the Network Merchants 3-step response.
     *
     * @return array
     */
    public function showNetworkMerchantsToken(Order $order)
    {
        return [
            'nmi_token' => true,
            'token_id' => request('token-id', null),
        ];
    }

    /**
     * Finalize the Order and process the payment.
     *
     * @return \Illuminate\Http\Response
     */
    public function processPayment(Order $order)
    {
        if ($order->is_paid) {
            return $this->errorResponse('Donation has already been processed.');
        }

        try {
            $response = app('nmi')->completeTransaction(request('token_id'));
        } catch (\Exception $e) {
            return $this->errorResponse('Payment failed. (Code: ' . $e->getCode() . ')');
        }

        if ($order->recurring_items) {
            if (! $order->member_id) {
                $order->createMember();
            }

            $paymentMethod = new PaymentMethod;
            $paymentMethod->member_id = $order->member_id;
            $paymentMethod->processor = 'networkmerchants';
            $paymentMethod->status = 'ACTIVE';
            $paymentMethod->account_type = ucwords(card_type_from_first_number($response['cc_number']));
            $paymentMethod->account_last_four = substr($response['cc_number'], -4);
            $paymentMethod->cc_expiry = Carbon::createFromFormat('my', $response['cc_exp']);
            $paymentMethod->display_name = $paymentMethod->account_type;
            $paymentMethod->token = $response['customer_vault_id'];
            $paymentMethod->billing_first_name = $order->billing_first_name;
            $paymentMethod->billing_last_name = $order->billing_last_name;
            $paymentMethod->save();

            $response = [
                'integration' => $paymentMethod->processor,
                'response' => '1',
                'response_text' => 'APPROVED',
                'authorization_code' => '',
                'transaction_id' => '',
                'avs_response' => '',
                'cvv_response' => '',
                'order_id' => '',
                'type' => '',
                'response_code' => '',
                'customer_vault_id' => $paymentMethod->token,
                'billing_id' => '',
                'billing_first_name' => $paymentMethod->billing_first_name,
                'billing_last_name' => $paymentMethod->billing_last_name,
                'cc_number' => $paymentMethod->account_number,
                'cc_exp' => fromUtcFormat($paymentMethod->cc_expiry, 'my'),
                'ach_account' => '',
                'ach_routing' => '',
                'ach_type' => $paymentMethod->ach_account_type,
                'ach_entity' => $paymentMethod->ach_entity_type,
            ];

            if ($order->totalamount) {
                try {
                    $res = $paymentMethod->charge($order->totalamount, $order->currency_code);

                    // merge transaction data into payment method response
                    $response = array_merge($response, array_filter($res, 'strlen'));
                } catch (Throwable $e) {
                    return $this->errorResponse('Payment failed. (Code: ' . $e->getCode() . ')');
                }
            }
        }

        if ($response['response'] !== '1') {
            $order->savePaymentProcessorResponse($response);

            return $this->errorResponse('Payment failed. Reason: ' . $response['response_text']);
        }

        // complete order
        $order->confirmationdatetime = Carbon::now();
        $order->createddatetime = Carbon::now();
        $order->ordered_at = fromUtc($order->ordered_at ?? $order->createddatetime);
        $order->invoicenumber = $order->client_uuid;
        $order->is_processed = true;

        $order->savePaymentProcessorResponse($response);
        $order->save();

        $order->afterProcessed();

        return response([
            'confirmationnumber' => $order->confirmationnumber,
            'billingcardtype' => $order->billingcardtype,
            'billingcardlastfour' => $order->billingcardlastfour,
            'confirmationdatetime' => fromUtcFormat($order->confirmationdatetime, 'api'),
        ]);
    }

    /**
     * Update the Order with new information and send the receipts.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendReceipt(Order $order)
    {
        $this->updateCart($order);

        // Send the email receipt
        cart_send_customer_email($order->client_uuid);

        $order->notify();

        // Send the tax receipt
        (new IssueTaxReciept)->handle(new OrderWasCompleted($order));

        return response(['success' => true]);
    }

    /**
     * Update the cart.
     *
     * @param \Ds\Models\Order $cart
     */
    private function updateCart(Order $cart)
    {
        $cart->account_type_id = request('account_type_id', $cart->account_type_id);
        $cart->billing_title = request('billing_title', $cart->billing_title);
        $cart->billing_first_name = request('billing_first_name', $cart->billing_first_name);
        $cart->billing_last_name = request('billing_last_name', $cart->billing_last_name);
        $cart->billing_organization_name = request('billing_organization_name', $cart->billing_organization_name);
        $cart->billingphone = request('billing_phone', $cart->billingphone);
        $cart->billingemail = request('billing_email', $cart->billingemail);
        $cart->billingaddress1 = request('billing_address', $cart->billingaddress1);
        $cart->billingcity = request('billing_city', $cart->billingcity);
        $cart->billingstate = request('billing_state', $cart->billingstate);
        $cart->billingzip = request('billing_zip', $cart->billingzip);
        $cart->referral_source = request('referral_source', $cart->referral_source);
        $cart->email_opt_in = request('email_opt_in', $cart->email_opt_in ?? false);
        $cart->comments = request('comments', $cart->comments);

        if (strtolower($cart->referral_source) === 'other') {
            $cart->referral_source = request('referral_source_other', $cart->referral_source);
        }

        $cart->save();
    }

    protected function errorResponse($message)
    {
        return response(['success' => false, 'message' => $message], 500);
    }
}
