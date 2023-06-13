<?php

namespace Ds\Services;

use Ds\Domain\Commerce\Models\PaymentProvider;
use Ds\Domain\Theming\Liquid\Drops\SiteDrop;
use Ds\Repositories\AccountTypeRepository;
use Liquid\Context;

class GivecloudCoreConfigRepository
{
    /** @var \Liquid\Context|null */
    private $context;

    /** @var array|null */
    private $gateways;

    public function getConfig(array $config = []): array
    {
        $context = $this->getContext();

        $cards = [];
        foreach (str_getcsv(sys_get('cardtypes')) as $card) {
            switch ($card) {
                case 'v': $cards[] = 'visa'; break;
                case 'm': $cards[] = 'master-card'; break;
                case 'a': $cards[] = 'american-express'; break;
                case 'd': $cards[] = 'discover'; break;
            }
        }

        $gateways = $this->getGateways();

        return array_merge([
            'site' => sys_get('ds_account_name'),
            'host' => $context->get('site.host'),
            'name' => $context->get('site.name'),
            'account_id' => $context->get('account.id'),
            'cart_id' => $context->get('cart.id'),
            'context' => 'web',
            'csrf_token' => $context->get('site.csrf_token'),
            'testmode_token' => PaymentProvider::shouldUseTestmodeProvider() ? user()->getTestmodeToken() : null,
            'currency' => [
                'code' => $context->get('cart.currency.iso_code'),
                'symbol' => $context->get('cart.currency.symbol'),
            ],
            'money_with_currency' => $context->get('site.money_with_currency'),
            'currencies' => $context->get('site.currencies'),
            'locale' => $context->get('site.locale'),
            'provider' => data_get($gateways, 'credit_card.provider', 'givecloudtest'),
            'gateways' => [
                'credit_card' => data_get($gateways, 'credit_card.provider', false),
                'bank_account' => data_get($gateways, 'bank_account.provider', false),
                'paypal' => data_get($gateways, 'paypal.provider', false),
                'wallet_pay' => data_get($gateways, 'wallet_pay.provider', false),
            ],
            'supported_cardtypes' => $cards,
            'processing_fees' => [
                'cover' => (bool) sys_get('dcc_enabled'),
                'amount' => (float) sys_get('dcc_cost_per_order'),
                'rate' => (float) sys_get('dcc_percentage'),
                'using_ai' => (bool) sys_get('dcc_ai_is_enabled'),
            ],
            'account_types' => app(AccountTypeRepository::class)->getOnWebAccountTypeDrops(),
            'captcha_type' => $context->get('site.captcha_type'),
            'requires_captcha' => sys_get('int:ss_auth_attempts') === 0,
            'recaptcha_site_key' => $context->get('site.recaptcha_site_key'),
            'title_options' => $context->get('site.donor_title_options'),
            'referral_sources' => $context->get('site.referral_sources'),
            'billing_country_code' => sys_get('default_country'),
            'shipping_country_code' => sys_get('default_country'),
            'force_country' => $context->get('site.force_country'),
            'pinned_countries' => $context->get('site.pinned_countries'),
            'account' => member() ? $context->get('account') : null,
            'gift_aid' => sys_get('bool:gift_aid'),
            'script_src' => $this->getScriptSrc(),
        ], $config);
    }

    public function getGateways(): array
    {
        if ($this->gateways) {
            return $this->gateways;
        }

        $context = $this->getContext();

        $this->gateways = [
            'credit_card' => PaymentProvider::getCreditCardProvider(false),
            'bank_account' => PaymentProvider::getBankAccountProvider(false),
            'paypal' => PaymentProvider::getPayPalProvider(false),
            'wallet_pay' => PaymentProvider::getWalletPayProvider(false),
        ];

        if ($context->get('product.metadata.credit_card_provider')) {
            $this->gateways['credit_card'] = PaymentProvider::enabled()
                ->provider($context->get('product.metadata.credit_card_provider'))
                ->orderBy('provider', 'asc')
                ->first();
        }

        if ($context->get('product.metadata.bank_account_provider')) {
            $this->gateways['bank_account'] = PaymentProvider::enabled()
                ->provider($context->get('product.metadata.bank_account_provider'))
                ->orderBy('provider', 'asc')
                ->first();
        }

        return $this->gateways;
    }

    public function getScriptSrc(): string
    {
        $lastModified = file_exists(base_path('public/assets/js/core.js'))
            ? filemtime(base_path('public/assets/js/core.js'))
            : time();

        return secure_site_url(sprintf(
            'assets/js/core.js?v=%s',
            substr(sha1($lastModified), 0, 10),
        ));
    }

    public function getContext(): Context
    {
        if (empty($this->context)) {
            $this->setContext();
        }

        return $this->context;
    }

    public function setContext(Context $context = null): self
    {
        $this->context = $context ?? $this->createDefaultContext();
        $this->gateways = null;

        return $this;
    }

    private function createDefaultContext(): Context
    {
        return new Context([
            'cart' => ['currency' => currency()],
            'site' => new SiteDrop,
        ]);
    }
}
