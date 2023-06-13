<?php

namespace Tests\Feature\Backend;

use Ds\Models\HookDelivery;
use Tests\TestCase;

/**
 * @group backend
 * @group hooks
 */
class HookDeliveryControllerTest extends TestCase
{
    public function testShowSuccess(): void
    {
        $hookDelivery = HookDelivery::factory()->create();

        $this->actingAsUser($this->createUserWithPermissions('hooks.index'));
        $response = $this->get(route('backend.settings.hook_deliveries.show', $hookDelivery->getKey()));

        $response->assertOk();
        $response->assertSeeText($hookDelivery->payload_json_pretty);
    }

    public function testShowNotFound(): void
    {
        $this->actingAsUser($this->createUserWithPermissions('hooks.index'));
        $this->get(route('backend.settings.hook_deliveries.show', 0))->assertNotFound();
    }
}
