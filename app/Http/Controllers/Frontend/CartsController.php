<?php

namespace Ds\Http\Controllers\Frontend;

use Ds\Domain\Commerce\Contracts\ProvidesTokenId;
use Ds\Domain\Commerce\Gateways;
use Ds\Domain\Theming\Liquid\Drop;
use Ds\Models\Order;
use Ds\Repositories\AccountTypeRepository;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CartsController extends Controller
{
    /**
     * Register controller middleware.
     */
    protected function registerMiddleware()
    {
        $this->middleware('requires.feature:givecloud_pro', ['only' => ['viewCart']]);
    }

    /**
     * View the cart.
     *
     * @param \Ds\Repositories\AccountTypeRepository $accountTypeRepository
     * @return string
     */
    public function viewCart(AccountTypeRepository $accountTypeRepository)
    {
        $title = __('frontend/cart.my_cart', ['name' => sys_get('cart_synonym')]);
        pageSetup($title, 'productList', 8);

        $order = Order::getActiveSession();

        return $this->renderTemplate('cart', [
            'checkout' => Drop::factory($order, 'Checkout'),
            'account_types' => $accountTypeRepository->getOnWebAccountTypeDrops(),
        ]);
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function switchCurrency($currency)
    {
        $order = Order::getActiveSession();

        try {
            $order->currency_code = $currency;
        } catch (InvalidArgumentException $e) {
            return redirect()->back()->with('liquid_req.error', 'Unsupported currency.');
        }

        $order->save();

        $order->calculate();
        $order->reapplyPromos();

        return redirect()->back();
    }

    /**
     * @return \Illuminate\Http\Response|array
     */
    public function tokenizeReturn(Order $cart)
    {
        $cart->validate([
            'unpaid',
            'presence_of_payment_provider',
        ]);

        $data = [];

        if ($cart->paymentProvider->gateway instanceof ProvidesTokenId) {
            $data['token_id'] = $cart->paymentProvider->gateway->getTokenId();
        }

        if ($cart->paymentProvider->gateway instanceof Gateways\GoCardlessGateway) {
            $cart->validate(['presence_of_payment_method']);

            return $this->chargeCart($cart);
        }

        if ($cart->paymentProvider->gateway instanceof Gateways\NMIGateway) {
            $data['token_id'] = request('token-id');
        }

        if ($cart->paymentProvider->gateway instanceof Gateways\PayPalExpressGateway) {
            if (! $cart->isForFundraisingForm()) {
                return $this->chargeCart($cart);
            }

            $data['token_id'] = request('token') . '|' . request('PayerID');
        }

        if ($cart->paymentProvider->gateway instanceof Gateways\VancoGateway) {
            $cart->validate(['presence_of_payment_method']);

            $res = $cart->paymentProvider->createSourceToken($cart->paymentMethod);
            $cart->paymentMethod->updateWithTransactionResponse($res);

            $data['token_id'] = request('token-id', $cart->paymentMethod->token);
        }

        $cart->response_text = null;
        $cart->single_use_token = $data['token_id'] ?? null;
        $cart->save();

        if (request()->wantsJson()) {
            return $data;
        }

        return response('<textarea>' . json_encode($data) . '</textarea>');
    }

    /**
     * @param \Ds\Models\Order $cart
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    private function chargeCart(Order $cart)
    {
        $res = (new API\CheckoutsController)->chargeToken($cart);

        if ($res->status() === 302) {
            return $res;
        }

        $context = request('rt_context');

        if (Str::startsWith($context, 'embeddable.donation:')) {
            $productCode = Str::after($context, 'embeddable.donation:');
            $failureUrl = route('embeddable.donation', [$productCode, 'state' => 'error']);
            $successUrl = route('embeddable.donation', [$productCode, 'state' => 'thankyou']);
        } else {
            $failureUrl = '/cart/#/payment';
            $successUrl = route('frontend.orders.thank_you', $cart->client_uuid);
        }

        if ($res->status() >= 400) {
            session(['cart_uuid' => $cart->client_uuid]);

            return redirect()->to($failureUrl)
                ->with('error', data_get($res->original, 'error'));
        }

        return redirect()->to($successUrl);
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function tokenizeCancel(Order $cart)
    {
        $cart->validate([
            'unpaid',
            'presence_of_payment_provider',
        ]);

        if ($cart->paymentProvider->gateway instanceof Gateways\PayPalExpressGateway && $cart->isForFundraisingForm()) {
            $cart->response_text = 'PAYPAL_REQUEST_CANCELLED';
            $cart->save();

            return response('<textarea>' . json_encode(['error' => $cart->response_text]) . '</textarea>');
        }

        return redirect()->to(url('cart/#/payment'));
    }
}
