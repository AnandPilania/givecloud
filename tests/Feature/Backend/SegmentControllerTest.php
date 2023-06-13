<?php

namespace Tests\Feature\Backend;

use Ds\Domain\Sponsorship\Models\Segment;
use Tests\TestCase;

class SegmentControllerTest extends TestCase
{
    public function testDestroySuccessful(): void
    {
        $user = $this->createUserWithPermissions(['segment.edit']);
        $segment = Segment::factory()->by($user)->create();

        $this->actingAsUser($user);
        $response = $this->post(route('backend.segment.destroy', $segment));

        $response->assertRedirect();
        $response->assertLocation(route('backend.segment.index'));
    }

    public function testDestroyModelNotFoundSuccessful(): void
    {
        $user = $this->createUserWithPermissions(['segment.edit']);

        $this->actingAsUser($user);
        $response = $this->post(route('backend.segment.destroy', 0));

        $response->assertRedirect();
        $response->assertLocation(route('backend.segment.index'));
        $response->assertSessionHas('_flashMessages.error', "The segment to delete doesn't exist.");
    }
}
