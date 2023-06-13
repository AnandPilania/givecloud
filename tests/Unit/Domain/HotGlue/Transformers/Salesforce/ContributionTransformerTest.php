<?php

namespace Tests\Unit\Domain\HotGlue\Transformers\Salesforce;

use Ds\Domain\HotGlue\Transformers\Salesforce\ContributionTransformer;
use Ds\Models\Order;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

/**
 * @group hotglue
 */
class ContributionTransformerTest extends TestCase
{
    public function testTransformerReturnsArrayOfValues(): void
    {
        $order = Order::factory()->paid()->createQuietly();
        $data = $this->app->make(ContributionTransformer::class)->transform($order);

        $this->assertIsArray($data);

        $this->assertSame('Contribution #' . $order->invoicenumber, $data['title']);
        $this->assertSame($order->currency_code, $data['currency']);
        $this->assertSame($order->balance_amt, $data['monetary_amount']);
        $this->assertSame('Open', $data['status']);
        $this->assertSame(100, $data['win_probability']);

        $this->assertArrayNotHasKey('contact_external_id', $data);
    }

    public function testTransformerAddsContactWhenAvailableAndExternalIdIsProvided(): void
    {
        Event::fake();

        sys_set('salesforce_contact_external_id', 'external_contact_id__c');

        $order = Order::factory()->paid()->createQuietly();

        $data = $this->app->make(ContributionTransformer::class)->transform($order);

        $this->assertIsArray($data);
        $this->assertSame($order->member->hashid, data_get($data, 'contact_external_id.value'));
    }
}
