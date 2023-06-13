<?php

namespace Ds\Http\Controllers\Frontend;

use Ds\Domain\Commerce\Contracts\ProvidesTokenId;
use Ds\Domain\Commerce\Gateways;
use Ds\Domain\Shared\Exceptions\DisclosableException;
use Ds\Models\PaymentMethod;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class PaymentMethodsController extends Controller
{
    /**
     * Register controller middleware.
     */
    protected function registerMiddleware()
    {
        $this->middleware('auth.member', ['except' => [
            'tokenizeReturn',
            'tokenizeCancel',
        ]]);
    }

    public function index()
    {
        pageSetup(__('frontend/accounts.payment_methods.index.edit_or_delete_payment_method'));

        return $this->renderTemplate('accounts/payment_methods/index');
    }

    public function show($id = null)
    {
        $template = 'accounts/payment_methods/edit';

        // look for a payment method belonging to the
        // authenticated member
        if ($id) {
            $payment_method = member()
                ->paymentMethods()
                ->find($id);

            if (! $payment_method) {
                $this->flash->error(__('frontend/accounts.payment_methods.payment_method_not_found'));

                return redirect()->to('account/payment_methods');
            }

            // otherwise, create a new payment method
        } else {
            $template = 'accounts/payment_methods/add';
            $payment_method = member()->newPaymentMethod();
        }

        pageSetup(__($id ? 'frontend/accounts.payment_methods.edit.edit_payment_method' : 'frontend/accounts.payment_methods.edit.add_payment_method'), 'content');

        return $this->renderTemplate($template, [
            'payment_method' => $payment_method,
            'all_countries' => cart_countries(),
            'states' => DB::select("SELECT id, code, name, country FROM region WHERE country = 'US' ORDER BY country DESC, code"),
            'provinces' => DB::select("SELECT id, code, name, country FROM region WHERE country = 'CA' ORDER BY country DESC, code"),
        ]);
    }

    public function save($id)
    {
        $paymentMethod = member()->paymentMethods()->findOrFail($id);

        if (! request('display_name')) {
            $this->flash->error(__('frontend/accounts.payment_methods.please_provide_a_name'));

            return redirect()->back();
        }

        $paymentMethod->display_name = request('display_name');
        $paymentMethod->save();

        $this->flash->success(__('frontend/accounts.payment_methods.payment_method_successfully_updated'));

        return redirect()->to('account/payment_methods');
    }

    /**
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response|\Illuminate\Http\JsonResponse|array
     */
    public function tokenizeReturn($id)
    {
        $paymentMethod = PaymentMethod::where('status', 'PENDING')->findOrFail($id);

        $data = [];

        if ($paymentMethod->paymentProvider->gateway instanceof ProvidesTokenId) {
            $data['token_id'] = $paymentMethod->paymentProvider->gateway->getTokenId();
        }

        if ($paymentMethod->paymentProvider->gateway instanceof Gateways\GoCardlessGateway) {
            return $this->createSourceToken($paymentMethod);
        }

        if ($paymentMethod->paymentProvider->gateway instanceof Gateways\NMIGateway) {
            $data['token_id'] = request('token-id');
        }

        if ($paymentMethod->paymentProvider->gateway instanceof Gateways\PayPalExpressGateway) {
            return $this->createSourceToken($paymentMethod);
        }

        if ($paymentMethod->paymentProvider->gateway instanceof Gateways\VancoGateway) {
            try {
                $res = $paymentMethod->paymentProvider->createSourceToken($paymentMethod);
                $paymentMethod->updateWithTransactionResponse($res);
            } catch (DisclosableException $e) {
                return response()->json(['error' => $e->getMessage()], 422);
            }

            $data['token_id'] = request('token-id', $paymentMethod->token);
        }

        if (request()->wantsJson()) {
            return $data;
        }

        return response('<textarea>' . json_encode($data) . '</textarea>');
    }

    /**
     * @param \Ds\Models\PaymentMethod $paymentMethod
     * @return \Illuminate\Http\RedirectResponse
     */
    private function createSourceToken(PaymentMethod $paymentMethod)
    {
        $context = request('rt_context');

        if (Str::startsWith($context, 'sms:')) {
            $url = '/' . str_replace(':', '/', $context);
        } else {
            $url = '/account/payment-methods';
        }

        try {
            $res = $paymentMethod->paymentProvider->createSourceToken($paymentMethod);
            $paymentMethod->updateWithTransactionResponse($res);
        } catch (DisclosableException $e) {
            return redirect()->to($url)->with('error', $e->getMessage());
        }

        return redirect()->to($url);
    }

    /**
     * @return \Illuminate\Http\RedirectResponse
     */
    public function tokenizeCancel($id)
    {
        return redirect()->to('/account/payment-methods');
    }

    public function delete($id)
    {
        $payment_method = member()
            ->paymentMethods()
            ->where('id', $id)
            ->first();

        if ($payment_method->delete() === false) {
            $this->flash->error(__('frontend/accounts.payment_methods.error_removing_payment_method'));

            return redirect()->to('account/payment_methods');
        }

        member_notify_updated_profile(
            member('id'),
            null,
            [__('frontend/accounts.payment_methods.payment_method_was_removed', [
                'account_type' => $payment_method->account_type,
                'account_last_four' => $payment_method->account_last_four,
            ])]
        );

        $this->flash->success(__('frontend/accounts.payment_methods.successfully_removed_payment_method'));

        return redirect()->to('account/payment_methods');
    }

    public function useAsDefault($id)
    {
        $payment_method = member()
            ->paymentMethods()
            ->where('id', $id)
            ->first();

        try {
            $payment_method->useAsDefaultPaymentMethod();
        } catch (Throwable $e) {
            $this->flash->error(__('frontend/accounts.payment_methods.problem_setting_default_payment_method'));

            return redirect()->to('account/payment_methods');
        }

        $this->flash->success(__('frontend/accounts.payment_methods.successfully_set_default_payment_method'));

        return redirect()->to('account/payment_methods');
    }
}
