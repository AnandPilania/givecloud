<?php

namespace Tests\Unit\Http\Resources;

use Ds\Http\Resources\TransactionResource;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Request;
use Tests\Concerns\InteractsWithRpps;
use Tests\TestCase;

class TransactionResourceTest extends TestCase
{
    use InteractsWithRpps;
    use WithFaker;

    public function testTransactionToArray(): void
    {
        $transaction = $this->createTransactionWithRPP();

        $result = (new TransactionResource($transaction))->toArray(new Request());

        $this->assertArrayHasKey('id', $result);
        $this->assertSame($transaction->prefixed_id, $result['id']);

        $this->assertArrayHasKey('contribution_number', $result);
        $this->assertSame($transaction->transaction_id, $result['contribution_number']);

        $this->assertArrayHasKey('currency', $result);
        $this->assertSame($transaction->currency_code, $result['currency']);

        $this->assertArrayHasKey('is_paid', $result);
        $this->assertTrue($result['is_paid']);

        $this->assertArrayHasKey('ordered_at', $result);
        $this->assertArrayHasKey('updated_at', $result);

        $this->assertArrayHasKey('supporter', $result);
        $this->assertArrayHasKey('payments', $result);
        $this->assertArrayHasKey('tax_lines', $result);
        $this->assertArrayHasKey('email', $result);
        $this->assertArrayHasKey('billing_address', $result);
        $this->assertArrayHasKey('shipping_method', $result);
        $this->assertArrayHasKey('shipping_address', $result);
    }
}
