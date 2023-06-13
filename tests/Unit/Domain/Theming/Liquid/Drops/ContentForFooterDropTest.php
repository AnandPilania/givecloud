<?php

namespace Tests\Unit\Domain\Theming\Liquid\Drops;

use Ds\Domain\Commerce\Models\PaymentProvider;
use Ds\Domain\Theming\Liquid\Drop;
use Ds\Domain\Theming\Liquid\Drops\ContentForFooterDrop;
use Liquid\Context;
use Tests\TestCase;

class ContentForFooterDropTest extends TestCase
{
    public function testOverridingPaymentProviderOnProductPages(): void
    {
        $paymentProvider = PaymentProvider::factory()->create();
        $nmiPaymentProvider = PaymentProvider::factory()->nmi()->create();

        sys_set([
            'credit_card_provider' => $paymentProvider->provider,
            'bank_account_provider' => $paymentProvider->provider,
        ]);

        $output = (string) new ContentForFooterDrop($this->generateLiquidContext([
            'product' => [
                'metadata' => [
                    'credit_card_provider' => $nmiPaymentProvider->provider,
                    'bank_account_provider' => $nmiPaymentProvider->provider,
                ],
            ],
        ]));

        $this->assertStringContainsString(
            '"gateways":{"credit_card":"nmi","bank_account":"nmi","paypal":false,"wallet_pay":"givecloudtest"}',
            $output
        );
    }

    private function generateLiquidContext(array $assigns): Context
    {
        $defaultAssigns = [
            'site' => Drop::factory(null, 'Site'),
            'settings' => Drop::factory(null, 'Settings'),
        ];

        return new Context(array_merge($defaultAssigns, $assigns), [
            'assets' => [
                'css' => [],
                'js' => [],
            ],
            'javascript' => [],
            'localizations' => [],
        ]);
    }
}
