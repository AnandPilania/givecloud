<?php

namespace Ds\Http\Controllers\API;

use Carbon\Carbon;
use Ds\Domain\Commerce\Exceptions\TransactionException;
use Ds\Domain\Commerce\Models\PaymentProvider;
use Ds\Domain\Commerce\Responses\TransactionResponse;
use Ds\Domain\Theming\Liquid\Drop;
use Ds\Models\Order;
use Ds\Models\PaymentMethod;

class OrderController extends DonationController
{
    /**
     * Start a 3-step transaction.
     *
     * @return \Illuminate\Http\Response
     */
    public function startPayment(Order $order)
    {
        if ($order->is_paid) {
            return $this->errorResponse('This contribution has already been charged.');
        }

        if ($order->is_locked) {
            return $this->errorResponse('This contribution is locked as it is already being charged.');
        }

        if ($order->totalamount === 0 && $order->recurring_items === 0) {
            return $this->errorResponse('This contribution has no total amount to charge or recurring items.');
        }

        if ($order->recurring_items > 0) {
            $paymentMethod = new PaymentMethod;
            $paymentMethod->billing_first_name = $order->billing_first_name;
            $paymentMethod->billing_last_name = $order->billing_last_name;
            $paymentMethod->billing_email = $order->billingemail;
            $paymentMethod->billing_address1 = $order->billingaddress1;
            $paymentMethod->billing_address2 = $order->billingaddress2;
            $paymentMethod->billing_city = $order->billingcity;
            $paymentMethod->billing_state = $order->billingstate;
            $paymentMethod->billing_postal = $order->billingzip;
            $paymentMethod->billing_country = $order->billingcountry;
            $paymentMethod->billing_phone = $order->billingphone;

            try {
                $link = app('nmi')->addCustomerLink($paymentMethod, $this->url("order/{$order->id}/nmi_token"));
            } catch (\DomainException $e) {
                return $this->errorResponse($e->getMessage());
            }
        } else {
            try {
                $link = app('nmi')->createSaleLink($order, $this->url("order/{$order->id}/nmi_token"));
            } catch (\DomainException $e) {
                return $this->errorResponse($e->getMessage());
            }
        }

        $order->lock();

        return response([
            'order_id' => $order->id,
            'form_url' => $link,
        ]);
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
     * @param \Ds\Models\Order $order
     * @return \Illuminate\Http\JsonResponse
     */
    public function handpointTransaction(Order $order)
    {
        $provider = PaymentProvider::query()
            ->provider('paysafe')
            ->enabled()
            ->first();

        if (! data_get($provider, 'config.handpoint_enabled')) {
            return $this->errorResponse('Handpoint integration is not configured');
        }

        switch (request('cardEntryType')) {
            case 'MSR': $entryType = 'mag_stripe_reader'; break;
            case 'ICC': $entryType = 'integrated_circuit_card'; break;
            case 'CNP': $entryType = 'card_not_present'; break;
            default:    $entryType = 'card_not_present';
        }

        switch (request('verificationMethod')) {
            case 'SIGNATURE':     $verificationMethod = 'signature'; break;
            case 'PIN':           $verificationMethod = 'pin'; break;
            case 'PIN_SIGNATURE': $verificationMethod = 'pin_signature'; break;
            case 'FAILED':        $verificationMethod = 'failed'; break;
            case 'NOT_REQUIRED':  $verificationMethod = 'not_required'; break;
            default:              $verificationMethod = null;
        }

        // attempt to extract the last4 from the customer receipt
        if (preg_match('/>\*{4} \*{4} \*{4} (\d\d\d\d)</m', request('customerReceipt'), $last4)) {
            $last4 = $last4[1];
        } else {
            $last4 = null;
        }

        // TODO: use eFTTransactionID to lookup transaction in Paysafe and
        // retrieve more details transaction information

        $res = new TransactionResponse($provider, [
            'completed' => request('finStatus') === 'AUTHORISED',
            'response' => request('finStatus') === 'AUTHORISED' ? '1' : '2',
            'response_text' => request('statusMessage') ?: request('errorMessage') ?: request('finStatus'),
            'transaction_id' => request('eFTTransactionID'),
            'account_type' => request('cardSchemeName'),
            'cc_number' => request('maskedCardNumber', $last4),
            'cc_exp' => fromUtcFormat(request('expiryDateMMYY'), 'my'),
            'cc_entry_type' => $entryType,
            'cc_verification' => $verificationMethod,
            'token_type' => 'cards',
            'source_token' => request('cardToken'),
            'handpoint' => request()->input(),
        ]);

        $payment = $order->updateWithTransactionResponse($res);

        if (request('signature')) {
            $payment->signature = request('signature');
            $payment->save();
        }

        if (! $res->isCompleted()) {
            $order->response_text = (new TransactionException($res))->getMessage();
            $order->save();

            app('activitron')->increment('Site.payments.failure');

            return $this->errorResponse($order->response_text);
        }

        if ($res->getSourceToken()) {
            if (! $order->member_id) {
                // a first/last/company name or email is required in order
                // for the createMember() call to successfully create a member
                $order->billing_first_name = $order->billing_first_name ?? 'Anonymous';
                $order->createMember();
            }

            $paymentMethod = new PaymentMethod;
            $paymentMethod->member_id = $order->member_id;
            $paymentMethod->billing_first_name = $order->billing_first_name;
            $paymentMethod->billing_last_name = $order->billing_last_name;
            $paymentMethod->billing_email = $order->billingemail;
            $paymentMethod->billing_address1 = $order->billingaddress1;
            $paymentMethod->billing_address2 = $order->billingaddress2;
            $paymentMethod->billing_city = $order->billingcity;
            $paymentMethod->billing_state = $order->billingstate;
            $paymentMethod->billing_postal = $order->billingzip;
            $paymentMethod->billing_country = $order->billingcountry;
            $paymentMethod->billing_phone = $order->billingphone;
            $paymentMethod->updateWithTransactionResponse($res);

            $order->payment_method_id = $paymentMethod->id;
            $order->save();
        }

        $order->confirmationdatetime = Carbon::now();
        $order->createddatetime = Carbon::now();
        $order->invoicenumber = $order->client_uuid;
        $order->is_processed = true;
        $order->save();

        $order->afterProcessed();

        app('activitron')->increment('Site.payments.success');

        return response()->json(Drop::factory($order, 'Checkout'));
    }

    /**
     * Return the packing slip as a base64 encoded PDF.
     *
     * @return string
     */
    public function getReceipt(Order $order)
    {
        $pdf = app('pdf')->loadView('orders/packing_slip', [
            'orders' => collect([$order]),
        ])->setOptions([
            'print-media-type' => true,
        ]);

        return $pdf->toDataUri();
    }
}
