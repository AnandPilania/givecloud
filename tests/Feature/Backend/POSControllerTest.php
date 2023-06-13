<?php

namespace Tests\Feature\Backend;

use Ds\Models\Order;
use Tests\TestCase;

class POSControllerTest extends TestCase
{
    public function testNewOrderAnonymousByDefault()
    {
        $res = $this->actingAsUser($this->createUserWithPermissions('pos.edit'))
            ->post(route('backend.pos.new'));

        $res->assertJsonPath('order.is_anonymous', true);
    }

    public function testNewAnonymousOrder()
    {
        $res = $this->actingAsUser($this->createUserWithPermissions('pos.edit'))
            ->post(route('backend.pos.new'), ['is_anonymous' => true]);

        $res->assertJsonPath('order.is_anonymous', true);
    }

    public function testNewPublicOrder()
    {
        $res = $this->actingAsUser($this->createUserWithPermissions('pos.edit'))
            ->post(route('backend.pos.new'), ['is_anonymous' => false]);

        $res->assertJsonPath('order.is_anonymous', false);
    }

    public function testMakeOrderAnonymous()
    {
        $order = Order::factory()->public()->create();

        $res = $this->actingAsUser($this->createUserWithPermissions('pos.edit'))
            ->post(route('backend.pos.update', $order), ['is_anonymous' => true]);

        $res->assertJsonPath('order.is_anonymous', true);
    }

    public function testMakeOrderPublic()
    {
        $order = Order::factory()->anonymous()->create();

        $res = $this->actingAsUser($this->createUserWithPermissions('pos.edit'))
            ->post(route('backend.pos.update', $order), ['is_anonymous' => false]);

        $res->assertJsonPath('order.is_anonymous', false);
    }
}
