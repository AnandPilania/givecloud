<?php

namespace Tests\Unit\Domain\QuickStart;

use Ds\Domain\QuickStart\QuickStartService;
use Tests\TestCase;

/** @group QuickStart */
class QuickStartServiceTest extends TestCase
{
    public function testTasksReturnsCollectionOfTasks(): void
    {
        $tasks = $this->app->make(QuickStartService::class)->tasks();

        $this->assertArrayHasKey('setup', $tasks->all());
        $this->assertArrayHasKey('goingLive', $tasks->all());
        $this->assertArrayHasKey('next', $tasks->all());

        $this->assertIsArray($tasks->get('setup'));
        $this->assertIsArray($tasks->get('goingLive'));
        $this->assertIsArray($tasks->get('next'));
    }

    public function testToArrayReturnsArrayOfArrays(): void
    {
        $tasks = $this->app->make(QuickStartService::class)->toArray();

        $this->assertArrayHasArrayWithValue($tasks['setup'], 'branding_setup', 'key');
        $this->assertArrayHasArrayWithValue($tasks['setup'], 'donation_item', 'key');
        $this->assertArrayHasArrayWithValue($tasks['setup'], 'donor_perfect_integration', 'key');
        $this->assertArrayHasArrayWithValue($tasks['setup'], 'tax_receipts', 'key');
        $this->assertArrayHasArrayWithValue($tasks['setup'], 'tax_receipt_templates', 'key');

        $this->assertArrayHasArrayWithValue($tasks['goingLive'], 'test_transactions', 'key');
        $this->assertArrayHasArrayWithValue($tasks['goingLive'], 'setup_live_gateway', 'key');
        $this->assertArrayHasArrayWithValue($tasks['goingLive'], 'choose_plan', 'key');
        $this->assertArrayHasArrayWithValue($tasks['goingLive'], 'turn_on_live_gateway', 'key');

        $this->assertArrayHasArrayWithValue($tasks['next'], 'custom_emails', 'key');
    }

    public function testToJsonReturnsJsonString(): void
    {
        $tasks = $this->app->make(QuickStartService::class)->toJson();

        $this->assertJson($tasks);
    }
}
