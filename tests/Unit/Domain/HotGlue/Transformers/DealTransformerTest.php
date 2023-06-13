<?php

namespace Tests\Unit\Domain\HotGlue\Transformers;

use Ds\Domain\HotGlue\Transformers\DealTransformer;
use Ds\Models\Order;
use Tests\TestCase;

/**
 * @group hotglue
 */
class DealTransformerTest extends TestCase
{
    public function testTransformerReturnsArrayOfValues(): void
    {
        $order = Order::factory()->paid()->createQuietly();

        $data = $this->app->make(DealTransformer::class)->transform($order);

        $this->assertIsArray($data);

        $this->assertSame('Contribution', $data['type']);
        $this->assertSame('Contribution #' . $order->invoicenumber, $data['title']);
        $this->assertSame($order->ordered_at->toApiFormat(), $data['close_date']);
        $this->assertSame($order->currency_code, $data['currency']);
        $this->assertSame($order->balance_amt, $data['monetary_amount']);
        $this->assertSame('Open', $data['status']);
        $this->assertSame(100, $data['win_probability']);

        $this->assertArrayNotHasKey('contact_external_id', $data);
    }
}
