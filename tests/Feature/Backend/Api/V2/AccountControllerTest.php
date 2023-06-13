<?php

namespace Tests\Feature\Backend\Api\V2;

use Ds\Models\Member;
use Ds\Models\Membership;
use Tests\TestCase;

/**
 * @group api
 */
class AccountControllerTest extends TestCase
{
    public function testIndexSuccess(): void
    {
        $accounts = Member::factory(3)
            ->hasGroups(
                Membership::factory()
                    ->started()
                    ->dpMembership()
                    ->count(mt_rand(1, 3))
            )->create();

        /** @var \Illuminate\Testing\TestResponse */
        $response = $this
            ->actingAsPassportUser($this->createUserWithPermissions(['member.']))
            ->getJson(route('admin.api.v2.supporters.index'))
            ->assertOk();

        $jsonResponse = $response->decodeResponseJson();
        $jsonResponse->assertStructure(['data', 'links', 'meta']);
        $jsonResponse->assertCount($accounts->count(), 'data');
    }

    public function testIndexFailsForGuest(): void
    {
        $this->getJson(route('admin.api.v2.supporters.index'))
            ->assertUnauthorized();
    }

    public function testIndexFailsWithoutPermission(): void
    {
        $this->actingAsPassportUser()
            ->getJson(route('admin.api.v2.supporters.index'))
            ->assertForbidden();
    }

    public function testShowSuccess(): void
    {
        Member::factory(3)->create();
        $accountToGet = Member::factory()->create();

        $jsonResponse = $this
            ->actingAsPassportUser($this->createUserWithPermissions(['member.']))
            ->getJson(route('admin.api.v2.supporters.show', $accountToGet->hashid))
            ->assertOk()
            ->decodeResponseJson();

        $jsonResponse->assertStructure(['data']);
        $this->assertSame($accountToGet->hashid, $jsonResponse->json('data.id'));
    }

    public function testShowFailsForGuest(): void
    {
        $this
            ->getJson(route('admin.api.v2.supporters.show', Member::factory(3)->create()->first()->hashid))
            ->assertUnauthorized();
    }

    public function testShowFailsWithoutPermission(): void
    {
        $this
            ->actingAsPassportUser()
            ->getJson(route('admin.api.v2.supporters.show', Member::factory(3)->create()->first()->hashid))
            ->assertForbidden();
    }
}
