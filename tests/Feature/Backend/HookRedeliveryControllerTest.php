<?php

namespace Tests\Feature\Backend;

use Ds\Models\HookDelivery;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * @group backend
 * @group hooks
 */
class HookRedeliveryControllerTest extends TestCase
{
    public function testStoreSuccess(): void
    {
        $hookDelivery = HookDelivery::factory()->create();

        Http::fake([
            $hookDelivery->hook->payload_url => Http::response('Ok.'),
        ]);

        $this->actingAsUser($this->createUserWithPermissions('hooks.edit'));
        $response = $this->post(route('backend.settings.hook_redelivery.store', $hookDelivery->getKey()));

        $response->assertOk();
        $response->assertSeeText($hookDelivery->payload_json_pretty);
    }

    public function testStoreNotFound(): void
    {
        $this->actingAsUser($this->createUserWithPermissions('hooks.edit'));
        $this->post(route('backend.settings.hook_redelivery.store', 0))->assertNotFound();
    }
}
