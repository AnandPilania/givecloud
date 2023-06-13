<?php

namespace Ds\Domain\Messenger;

use BotMan\BotMan\BotMan as BotManInterface;
use BotMan\BotMan\Messages\Conversations\Conversation as BotManConversation;
use Closure;
use Ds\Domain\Commerce\Enums\CredentialOnFileInitiatedBy;
use Ds\Domain\Commerce\Exceptions\TransactionException;
use Ds\Domain\Commerce\Responses\TransactionResponse;
use Ds\Domain\Commerce\SourceTokenChargeOptions;
use Ds\Domain\Messenger\Models\Conversation as EloquentConversation;
use Ds\Domain\Messenger\Models\ResumableConversation;
use Ds\Domain\Shared\Exceptions\DisclosableException;
use Ds\Models\Member as Account;
use Ds\Models\Order;
use Illuminate\Support\Str;
use libphonenumber\NumberParseException;
use Throwable;

abstract class Conversation extends BotManConversation
{
    /** @var string */
    public const AMOUNT_PARAMETER_REGEX = '[£€$0-9,.]*';

    /** @var int */
    protected $conversationId;

    /** @var array */
    protected $parameters = [];

    /** @var int */
    protected $accountId;

    /** @var string */
    protected $defaultErrorMessage = "Thanks for your interest. Unfortunately we weren't able to process your request due to an error.";

    /** @var string */
    protected $modelNotFoundErrorMessage = "Thanks for your interest! Sorry we can't process your request to text in a donation.";

    /**
     * Create an instance.
     *
     * @param \Ds\Domain\Messenger\Models\Conversation $conversation
     * @param array $parameters
     */
    public function __construct(EloquentConversation $conversation, array $parameters = [])
    {
        $this->conversationId = $conversation->id;
        $this->parameters = $parameters;
    }

    /**
     * Handle the conversation.
     */
    abstract public function handle();

    /**
     * Get the configuration for the conversation.
     *
     * @return array
     */
    abstract public static function configuration(): array;

    /**
     * Get the conversation type.
     *
     * @return string
     */
    public static function getConversationType(): string
    {
        $type = preg_replace('/^.*\\\\([^\\\\]+)Conversation$/', '$1', get_called_class());

        return Str::snake($type);
    }

    /**
     * Set the BotMan instance.
     *
     * @param \BotMan\BotMan\BotMan $bot
     */
    public function setBot(BotManInterface $bot)
    {
        // Due to closure serialization issues we are avoiding storing
        // dependancies that include references to the Laravel IoC container
        // directly on the conversation

        $this->bot = new SerializableBotMan;
    }

    /**
     * Get the conversation cache time.
     *
     * @return int
     */
    public function getConversationCacheTime()
    {
        return $this->cacheTime ?? config('botman.config.conversation_cache_time');
    }

    /**
     * Set the account model.
     *
     * @param \Ds\Models\Member $account
     */
    public function setAccount(Account $account = null)
    {
        $this->accountId = $account->id ?? null;
    }

    /**
     * Get the account model.
     *
     * @return \Ds\Models\Member|null
     */
    public function getAccount()
    {
        if (empty($this->accountId)) {
            // for SMS messages the user id will be:
            //  - Nexmo  : MSISDN without (+) sign
            //  - Twilio : E.164 formatted number
            $userId = $this->bot->getUser()->getId();

            try {
                $accounts = Account::active()->billPhoneE164($userId)->get();
            } catch (NumberParseException $e) {
                $accounts = collect();
            }

            if ($accounts->count() > 1) {
                $account = $accounts->firstWhere('sms_verified', true);
            } elseif ($accounts->count() === 1) {
                $account = $accounts->first();
                $account->sms_verified = true;
                $account->save();
            } else {
                $account = null;
            }

            $this->setAccount($account);
        }

        if (empty($this->accountId)) {
            return;
        }

        // When BotMan serializes the Conversation having references to Eloquent
        // models can cause issues when SuperClosure serializes the closure scopes
        return reqcache("botman-conversation:account-{$this->accountId}", function () {
            return Account::find($this->accountId);
        });
    }

    /**
     * Get the conversation model.
     *
     * @return \Ds\Domain\Messenger\Models\Conversation
     */
    public function getConversation()
    {
        // When BotMan serializes the Conversation having references to Eloquent
        // models can cause issues when SuperClosure serializes the closure scopes
        return reqcache("botman-conversation:conversation-{$this->conversationId}", function () {
            return EloquentConversation::find($this->conversationId);
        });
    }

    /**
     * Starts a resumable conversation.
     */
    protected function startResumableConversation(array $replies, ?string $resumeOn = 'payment_method_added'): void
    {
        $message = $this->bot->getMessage();

        $resumableConversation = ResumableConversation::create([
            'driver' => get_class($this->bot->getDriver()),
            'sender' => $message->getSender(),
            'recipient' => $message->getRecipient(),
            'message' => $message->getText(),
            'conversation_id' => $this->conversationId,
            'parameters' => $this->parameters,
            'account_id' => $this->accountId,
            'resume_on' => $resumeOn,
            'expires' => $this->getConversationCacheTime(),
        ]);

        foreach ($replies as $key => $reply) {
            if ($key === array_key_last($replies)) {
                $reply = "$reply {$resumableConversation->permalink}";
            }

            $this->say($reply);
        }
    }

    /**
     * Run the conversation.
     *
     * @return void
     */
    public function run()
    {
        try {
            $this->handle();
        } catch (ResumableConversationException $e) {
            $this->startResumableConversation($e->getTextMessages(), $e->getResumeOn());
        } catch (Throwable $e) {
            report($e);
            $this->say($this->defaultErrorMessage);
        }
    }

    /**
     * Ask a YES or NO question.
     *
     * @param mixed $question
     * @param \Closure $yes
     * @param \Closure $no
     * @return $this
     */
    public function askYesOrNo($question, Closure $yes, Closure $no)
    {
        return $this->ask($question, [
            [
                'pattern' => '(yes|yep|y|ok|okay|sure|fine|ya)',
                'callback' => $yes,
            ], [
                'pattern' => '(nah|no|nope|n)',
                'callback' => $no,
            ], [
                'pattern' => '(.*)',
                'callback' => function () use ($question) {
                    if (is_string($question)) {
                        $this->repeat("Sorry, we didn't understand your reply. $question");
                    } else {
                        $this->repeat();
                    }
                },
            ],
        ]);
    }

    /**
     * Checks if the user has a verified account.
     *
     * @param string $resumeOn
     * @return bool
     */
    protected function requireVerifiedAccount($resumeOn = 'account_verified')
    {
        if ($this->getAccount()) {
            return true;
        }

        $message = sys_get('messenger_donation_welcome_message');

        if (empty($message)) {
            $message = "Welcome! Let's get you setup on Text-To-Give using this phone number. Tap the link to get started";
        }

        throw (new ResumableConversationException($resumeOn))->setTextMessages([$message]);
    }

    /**
     * Checks if the user has a verified account with a
     * default payment method attached to it.
     *
     * @param string $resumeOn
     * @return bool
     */
    protected function requireVerifiedAccountInGoodStanding($resumeOn = 'payment_method_added')
    {
        $this->requireVerifiedAccount($resumeOn);

        $account = $this->getAccount();

        if ($account->defaultPaymentMethod) {
            return true;
        }

        throw (new ResumableConversationException($resumeOn))->setTextMessages([
            'Looks like you may not have a payment method setup or there may be a problem with your saved payment method.',
            'Tap the link below to update your payment method.',
        ]);
    }

    /**
     * Create a new cart.
     *
     * @return \Ds\Models\Order
     */
    protected function createCart()
    {
        $account = $this->getAccount();
        $conversation = $this->getConversation();

        $cart = new Order;
        $cart->source = 'Messenger';
        $cart->client_uuid = uuid();
        $cart->tracking_source = $conversation->tracking_source;
        $cart->started_at = fromUtc('now');
        $cart->tax_receipt_type = sys_get('tax_receipt_type');
        $cart->currency_code = sys_get('dpo_currency');
        $cart->functional_currency_code = sys_get('dpo_currency');
        $cart->dcc_enabled_by_customer = false;
        $cart->dcc_type = null;

        $cart->populateMember($account);

        $cart->payment_type = 'payment_method';
        $cart->payment_method_id = $account->defaultPaymentMethod->id;
        $cart->payment_provider_id = $account->defaultPaymentMethod->paymentProvider->id;
        $cart->billing_first_name = $cart->billing_first_name ?: $account->defaultPaymentMethod->billing_first_name;
        $cart->billing_last_name = $cart->billing_last_name ?: $account->defaultPaymentMethod->billing_last_name;
        $cart->billingemail = $cart->billingemail ?: $account->defaultPaymentMethod->billing_email;
        $cart->billingaddress1 = $cart->billingaddress1 ?: $account->defaultPaymentMethod->billing_address1;
        $cart->billingaddress2 = $cart->billingaddress2 ?: $account->defaultPaymentMethod->billing_address2;
        $cart->billingcity = $cart->billingcity ?: $account->defaultPaymentMethod->billing_city;
        $cart->billingstate = $cart->billingstate ?: $account->defaultPaymentMethod->billing_state;
        $cart->billingzip = $cart->billingzip ?: $account->defaultPaymentMethod->billing_postal;
        $cart->billingcountry = $cart->billingcountry ?: $account->defaultPaymentMethod->billing_country;
        $cart->billingphone = $cart->billingphone ?: $account->defaultPaymentMethod->billing_phone;

        return $cart;
    }

    /**
     * Complete a cart.
     *
     * @param \Ds\Models\Order $cart
     */
    protected function completeCart(Order $cart)
    {
        $cart->validate([
            'unpaid',
            'presence_of_items',
            'minimum_order_total',
            'item_availability',
            'ach_requirements',
        ]);

        $handleException = function (Throwable $e) use ($cart) {
            $cart->response_text = $e->getMessage();
            $cart->save();

            app('activitron')->increment('Site.payments.failure');

            return $e;
        };

        if ($cart->totalamount) {
            try {
                $res = $cart->paymentMethod->charge(
                    $cart->totalamount,
                    $cart->currency_code,
                    new SourceTokenChargeOptions([
                        'dccAmount' => $cart->dcc_total_amount,
                        'contribution' => $cart,
                        'initiatedBy' => CredentialOnFileInitiatedBy::CUSTOMER,
                    ]),
                );
            } catch (TransactionException $e) {
                $cart->updateWithTransactionResponse($e->getResponse());
                throw $handleException($e);
            } catch (DisclosableException $e) {
                throw $handleException($e);
            }
        } else {
            $res = TransactionResponse::fromPaymentMethod($cart->paymentMethod);
        }

        $cart->updateWithTransactionResponse($res);

        $cart->confirmationdatetime = now();
        $cart->createddatetime = now();
        $cart->ordered_at = fromUtc($cart->ordered_at ?? $cart->createddatetime);
        $cart->invoicenumber = $cart->client_uuid;
        $cart->is_processed = true;
        $cart->save();

        $cart->afterProcessed();

        app('activitron')->increment('Site.payments.success');
    }
}
