<?php

namespace Tests\Feature\Backend;

use Ds\Models\Pledge;
use Illuminate\Http\Response;
use Tests\TestCase;

/**
 * @group backend
 * @group pledges
 */
class PledgesControllerTest extends TestCase
{
    public function testSaveNewPledgeSuccess(): void
    {
        sys_set('feature_pledges', 1);

        $pledge = Pledge::factory()->make();

        $response = $this->actingAsUser()
            ->postJson(route('backend.pledges.insert'), $pledge->toArray())
            ->assertOk();

        $this->assertSame('Pledge saved.', $response->json('success'));
        foreach ($pledge->toArray() as $attribute => $value) {
            $this->assertSame($value, $response->json("pledge.$attribute"));
        }
    }

    /**
     * @dataProvider saveNewPedgeFailsData
     */
    public function testSaveNewPledgeFails(array $pledgeOverrides): void
    {
        sys_set('feature_pledges', 1);

        $pledgeData = Pledge::factory()->make($pledgeOverrides)->toArray();

        $this->actingAsUser()
            ->postJson(route('backend.pledges.insert'), $pledgeData)
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);

        $this->assertSame(0, Pledge::where($pledgeData)->count());
    }

    public function saveNewPedgeFailsData(): array
    {
        return [
            [['account_id' => null]],
            [['pledge_campaign_id' => null]],
        ];
    }
}
