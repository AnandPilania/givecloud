<?php

namespace Tests\Unit\Domain\Commerce\Importers;

use Ds\Domain\Commerce\Importers\PayPalExpressImporter;
use Ds\Domain\Commerce\Models\PaymentProvider;
use Ds\Models\Member as Account;
use Ds\Models\Payment;
use Ds\Models\Product;
use Ds\Models\Variant;
use Illuminate\Support\Facades\File;
use PayPal\Core\PPUtils;
use PayPal\EBLBaseComponents\PaymentTransactionType;
use Tests\TestCase;

class PayPalExpressImporterTest extends TestCase
{
    public function testImportingPayerWithEmailBelongingToAnAccount(): void
    {
        $account = Account::factory()->create();
        $product = Product::factory()->create();
        $variant = Variant::factory()->create();
        $product->variants()->save($variant);

        $paymentTransaction = $this->getPayPalPaymentTransactionWithNoSubscription();
        $paymentTransaction->PayerInfo->Payer = $account->email;

        $payment = $this->importPaymentFromGateway($variant, $paymentTransaction);

        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertSame($account->getKey(), $payment->account->getKey());
    }

    public function testImportingPayerWithEmailNotBelongingToAnAccount(): void
    {
        $product = Product::factory()->create();
        $variant = Variant::factory()->create();
        $product->variants()->save($variant);

        $paymentTransaction = $this->getPayPalPaymentTransactionWithNoSubscription();
        $payment = $this->importPaymentFromGateway($variant, $paymentTransaction);

        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertSame($paymentTransaction->PayerInfo->Payer, $payment->account->email);
    }

    private function importPaymentFromGateway(Variant $variant, PaymentTransactionType $paymentTransaction): ?Payment
    {
        $importer = app()->makeWith(PayPalExpressImporter::class, [
            'provider' => PaymentProvider::factory()->paypalExpress()->create(),
            'onetimeVariantId' => $variant->getKey(),
        ]);

        return $importer->importPaymentFromGateway($paymentTransaction);
    }

    private function getPayPalPaymentTransactionWithNoSubscription(): PaymentTransactionType
    {
        return tap(new PaymentTransactionType, function ($paymentTransaction) {
            $paymentTransaction->init(PPUtils::xmlToArray(
                File::get(base_path('tests/fixtures/paypal/payment-transaction-type-with-no-subscription.xml'))
            ));
        });
    }
}
