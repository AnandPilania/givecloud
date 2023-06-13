<?php

namespace Tests\Unit\Services;

use Ds\Models\Member;
use Ds\Models\Order;
use Ds\Services\SupporterSearchService;
use Tests\TestCase;

class SupporterSearchServiceTest extends TestCase
{
    public function testCanFindSupporterAndHighlightsResult(): void
    {
        $term = 'tester';

        Member::factory(20)->create();

        $member = Member::factory()->create([
            'last_name' => 'Testerships',
        ]);

        $results = $this->app->make(SupporterSearchService::class)->handle($term);

        $this->assertCount(1, $results['results']);
        $this->assertSame($member->id, $results['results'][0]['id']);
        $this->assertStringContainsStringIgnoringCase($term, $results['results'][0]['name']);
        $this->assertStringContainsStringIgnoringCase("<mark>$term</mark>", $results['results'][0]['name']);
    }

    public function testLimitsToTenSupporters()
    {
        $term = 'some-shared-email@gc.org';

        Member::factory(20)->create([
            'email' => $term,
        ]);

        $results = $this->app->make(SupporterSearchService::class)->handle($term);

        $this->assertCount(10, $results['results']);
    }

    public function testCanFindSupporterUsingFullName(): void
    {
        $term = 'Philippe Perusse';

        $member = Member::factory()->create([
            'first_name' => 'Philippe',
            'last_name' => 'Perusse',
        ]);

        Member::factory(20)->create();

        $results = $this->app->make(SupporterSearchService::class)->handle($term);

        $this->assertCount(1, $results['results']);
        $this->assertSame($member->id, $results['results'][0]['id']);
    }

    public function testReturnsContributionWhenNoSupporterFound(): void
    {
        $partialTerm = 'factory.will.not.create@gc';

        Member::factory(20)->create();
        Order::factory(20)->paid()->create();

        $order = Order::factory()->paid()->create([
            'billingemail' => 'factory.will.not.create@gc.org',
        ]);

        $results = $this->app->make(SupporterSearchService::class)->handle($partialTerm);

        $this->assertFalse($results['supporters']);
        $this->assertCount(1, $results['results']);
        $this->assertStringContainsStringIgnoringCase($partialTerm, $results['results'][0]['email']);
        $this->assertSame($order->id, $results['results'][0]['id']);
    }

    /** @dataProvider supporterAttributesDataProvider */
    public function testCanSearchAgainstAttributes(string $attribute, string $term): void
    {
        Member::factory(20)->create();

        $member = Member::factory()->create([
            $attribute => $term,
        ]);

        $results = $this->app->make(SupporterSearchService::class)->handle($term);

        $this->assertSame($member->id, $results['results'][0]['id']);
        $this->assertCount(1, $results['results']);
    }

    public function supporterAttributesDataProvider(): array
    {
        return [
            ['first_name', 'Tester'],
            ['last_name', 'Testerships'],
            ['email', 'some.email.address@gc.org'],
            ['bill_email', 'some.email.address@gc.org'],
            ['ship_email', 'some.email.address@gc.org'],
            ['bill_phone', '1-888-TEST-BILL'],
            ['ship_phone', '1-888-TEST-SHIP'],
        ];
    }

    /** @dataProvider contributionsAttributeDataProvider */
    public function testCanSearchOnMultipleAttributesAndReturnsContributions(string $attribute, string $expectedAttribute, string $term): void
    {
        Member::factory(20)->create();
        Order::factory(20)->paid()->create();

        $order = Order::factory()->paid()->create([
            $attribute => $term,
        ]);

        $results = $this->app->make(SupporterSearchService::class)->handle($term);

        $this->assertFalse($results['supporters']);
        $this->assertCount(1, $results['results']);
        $this->assertSame($order->id, $results['results'][0]['id']);
        $this->assertStringContainsStringIgnoringCase($term, $results['results'][0][$expectedAttribute]);
    }

    public function contributionsAttributeDataProvider(): array
    {
        return [
            ['billing_first_name', 'name', 'Tester'],
            ['billing_last_name', 'name', 'Testerships'],
            ['billingemail', 'email', 'some.email.address@gc.org'],
            ['billingaddress1', 'address_line_1', '123 Test St'],
            ['billingphone', 'phone', '1-888-TEST-TEST'],
            ['shipping_first_name', 'name', 'Tester'],
            ['shipping_last_name', 'name', 'Testerships'],
            ['shipemail', 'email', 'some.email.address@gc.org'],
            ['shipaddress1', 'address_line_1', '123 Test St'],
        ];
    }
}
