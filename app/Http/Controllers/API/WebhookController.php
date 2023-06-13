<?php

namespace Ds\Http\Controllers\API;

use Ds\Domain\Commerce\Jobs\Webhooks;
use Ds\Domain\Commerce\Models\PaymentProvider;
use Ds\Domain\Messenger\BotMan;
use Ds\Domain\Messenger\Models\Conversation;
use Ds\Jobs\MuxWebhook;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Throwable;

class WebhookController extends Controller
{
    /**
     * Register controller middleware.
     */
    protected function registerMiddleware()
    {
        // do nothing
    }

    /**
     * Handle BotMan webhook.
     *
     * @param \Ds\Domain\Messenger\BotMan $botman
     */
    public function postBotMan(BotMan $botman)
    {
        $conversations = Conversation::where('enabled', true)->get();

        foreach ($conversations as $conversation) {
            $conversation->registerCommand($botman);
        }

        $botman->fallback(function ($bot) {
            $bot->reply("Sorry, we're not sure how to respond. Can you double check your message and try again?");
        });

        $botman->listen();
    }

    /**
     * Handle Authorize.Net webhook.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response|string
     */
    public function postAuthorizeNet(Request $request)
    {
        try {
            $provider = PaymentProvider::enabled()
                ->provider('authorizenet')
                ->firstOrFail();

            $signature = base64_encode(hash_hmac('sha512', (string) $request->getContent(), $provider->credential4, true));

            if (hash_equals($signature, $request->header('X-ANET-Signature')) === false) {
                return response('Bad.', 400);
            }

            dispatch(new Webhooks\AuthorizeNet($request->all()));
        } catch (ModelNotFoundException $e) {
            // do nothing
        }

        return 'Ok.';
    }

    /**
     * Handle PayPal webhook.
     *
     * @return \Illuminate\Http\Response|string
     */
    public function postPaypal()
    {
        try {
            $provider = PaymentProvider::enabled()
                ->provider('paypal')
                ->firstOrFail();

            $message = $provider->gateway->getIpnMessage();

            if ($message->validate() === false) {
                return response('Bad.', 400);
            }

            dispatch(new Webhooks\PayPalIpn($message));
        } catch (Throwable $e) {
            return response('Bad.', 400);
        }

        return 'Ok.';
    }

    /**
     * Handle Stripe webhook.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response|string
     */
    public function postStripe(Request $request)
    {
        try {
            $provider = PaymentProvider::enabled()
                ->provider('stripe')
                ->firstOrFail();

            $event = $provider->gateway->getWebhookEvent();

            dispatch(new Webhooks\Stripe($event));
        } catch (Throwable $e) {
            return response('Bad.', 400);
        }

        return 'Ok.';
    }

    /**
     * Handle Mux webhook.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response|string
     */
    public function postMux(Request $request)
    {
        try {
            dispatch(new MuxWebhook($request->all()));
        } catch (Throwable $e) {
            return response('Bad.', 400);
        }

        return 'Ok.';
    }
}
