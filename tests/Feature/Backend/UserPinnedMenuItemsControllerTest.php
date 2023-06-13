<?php

namespace Tests\Feature\Backend;

use Ds\Models\User;
use Tests\TestCase;

class UserPinnedMenuItemsControllerTest extends TestCase
{
    public function testInvokeControllerStoresMetadata()
    {
        $user = User::factory()->create();

        $items = [
            'supporters_fundraisers',
            'supporters_archived',
        ];

        $this->assertNull($user->metadata('pinned-menu-items'));

        $this->actingAs($user)
            ->post(route('backend.pin_menu_items.store'), ['menuItems' => $items])
            ->assertRedirect(route('backend.profile'));

        $this->assertSame($items, $user->metadata('pinned-menu-items'));
    }
}
