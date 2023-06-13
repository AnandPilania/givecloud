<?php

namespace Tests\Feature\Backend;

use Ds\Models\PledgeCampaign;
use Ds\Models\User;
use Tests\TestCase;

/**
 * @group backend
 * @group campaign
 */
class CampaignControllerTest extends TestCase
{
    public function testListingRoute()
    {
        $this->actingAsUser($this->createUserWithPermissions('pledgecampaigns.edit'));
        $response = $this->get(route('backend.campaign.index'));

        $response->assertSeeText('Pledge Campaigns');
    }

    public function testGetIndexJsonResults()
    {
        $newCampaign = PledgeCampaign::factory()->create();

        $this->actingAsUser($this->createUserWithPermissions('pledgecampaigns.edit'));
        $response = $this->post(route('backend.campaign.index_json'));

        $response->assertSeeText($newCampaign->name);
    }

    public function testGetIndexJsonResultsWithMatchingSearch()
    {
        $newCampaign = PledgeCampaign::factory()->create();

        $this->actingAsUser($this->createUserWithPermissions('pledgecampaigns.edit'));
        $response = $this->post(route('backend.campaign.index_json'), [
            'search' => $newCampaign->name,
        ]);

        $response->assertSeeText($newCampaign->name);
    }

    public function testGetIndexJsonResultsWithNoMatchingSearch()
    {
        $newCampaign = PledgeCampaign::factory()->create();

        $this->actingAsUser($this->createUserWithPermissions('pledgecampaigns.edit'));
        $response = $this->post(route('backend.campaign.index_json'), [
            'search' => 'Random String that Will Not Match',
        ]);

        $response->assertDontSee($newCampaign->name);
    }

    public function testViewCampaignModal()
    {
        $newCampaign = PledgeCampaign::factory()->create();

        $this->actingAsUser($this->createUserWithPermissions('pledgecampaigns.edit'));
        $response = $this->get(route('backend.campaign.modal_update', $newCampaign));

        $response->assertSeeText($newCampaign->name);
        $response->assertSeeText('Campaign Name');
    }

    public function testViewNewCampaignModal()
    {
        $this->actingAsUser($this->createUserWithPermissions('pledgecampaigns.edit'));
        $response = $this->get(route('backend.campaign.modal_new'));

        $response->assertSeeText('New Pledge Type');
        $response->assertSeeText('Campaign Name');
    }

    public function testInsertCampaign()
    {
        $campaignData = PledgeCampaign::factory()->make();

        $this->actingAsUser($this->createUserWithPermissions('pledgecampaigns.edit'));

        $this->post(route('backend.campaign.insert'), [
            'name' => $campaignData->name,
            'start_date' => $campaignData->start_date,
            'end_date' => $campaignData->end_date,
        ]);

        $campaign = PledgeCampaign::first();

        $this->assertSame($campaign->name, $campaignData->name);
        $this->assertSame($campaign->start_date->toDateTimeString(), $campaignData->start_date->toDateTimeString());
        $this->assertSame($campaign->end_date->toDateTimeString(), $campaignData->end_date->toDateTimeString());
    }

    public function testCannotInsertCampaignWithoutPermission()
    {
        $campaignData = PledgeCampaign::factory()->make();

        // User without `pledgecampaigns.edit` permission
        $this->actingAsUser(User::factory()->create());

        $response = $this->post(route('backend.campaign.insert'), [
            'name' => $campaignData->name,
            'start_date' => $campaignData->start_date,
            'end_date' => $campaignData->end_date,
        ]);

        $campaignCount = PledgeCampaign::count();

        $response->assertRedirect();
        $this->assertSame(0, $campaignCount);
    }

    public function testUpdateCampaign()
    {
        $campaign = PledgeCampaign::factory()->create();

        $newName = 'A New Title';
        $newStartDate = $campaign->end_date->copy()->addYear();
        $newEndDate = $newStartDate->copy()->addYear();

        $this->actingAsUser($this->createUserWithPermissions('pledgecampaigns.edit'));

        $this->post(route('backend.campaign.update', $campaign), [
            'name' => $newName,
            'start_date' => $newStartDate,
            'end_date' => $newEndDate,
        ]);

        $campaign->refresh();

        $this->assertSame($campaign->name, $newName);
        $this->assertSame($campaign->start_date->toDateTimeString(), $newStartDate->toDateTimeString());
        $this->assertSame($campaign->end_date->toDateTimeString(), $newEndDate->toDateTimeString());
    }

    public function testCannotUpdateCampaignWithoutPermission()
    {
        $campaign = PledgeCampaign::factory()->create();

        $originalName = $campaign->name;

        $newName = 'A New Title';

        // User without `pledgecampaigns.edit` permission
        $this->actingAsUser(User::factory()->create());

        $response = $this->post(route('backend.campaign.update', $campaign), [
            'name' => $newName,
            'start_date' => $campaign->start_date,
            'end_date' => $campaign->end_date,
        ]);

        $campaign->refresh();

        $this->assertNotSame($campaign->name, $newName);
        $this->assertSame($campaign->name, $originalName);
        $response->assertRedirect();
    }

    public function testDestroyCampaign()
    {
        $campaign = PledgeCampaign::factory()->create();

        $this->actingAsUser($this->createUserWithPermissions('pledgecampaigns.edit'));

        $preDestroyCampaignCount = PledgeCampaign::where('id', $campaign->id)->count();

        $this->get(route('backend.campaign.destroy', $campaign));

        $postDestroyCampaignCount = PledgeCampaign::where('id', $campaign->id)->count();

        $this->assertSame(1, $preDestroyCampaignCount);
        $this->assertSame(0, $postDestroyCampaignCount);
    }

    public function testCannotDestroyCampaignWithoutPermission()
    {
        $campaign = PledgeCampaign::factory()->create();

        // User without `pledgecampaigns.edit` permission
        $this->actingAsUser(User::factory()->create());

        $preDestroyCampaignCount = PledgeCampaign::where('id', $campaign->id)->count();

        $response = $this->get(route('backend.campaign.destroy', $campaign));

        $postDestroyCampaignCount = PledgeCampaign::where('id', $campaign->id)->count();

        $response->assertRedirect();
        $this->assertSame(1, $preDestroyCampaignCount);
        $this->assertSame(1, $postDestroyCampaignCount);
    }
}
