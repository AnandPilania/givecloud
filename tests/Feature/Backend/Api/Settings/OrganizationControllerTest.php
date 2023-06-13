<?php

namespace Tests\Feature\Backend\Api\Settings;

use Ds\Domain\MissionControl\MissionControlService;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/** @group ApiSettings */
class OrganizationControllerTest extends TestCase
{
    use WithFaker;

    public function testGetReturnsOrganizationResource(): void
    {
        $this->actingAsAdminUser();

        $this->get(route('api.settings.organization.show'))
            ->assertJsonStructure(['data' => [
                'org_legal_name',
                'org_legal_address',
                'org_legal_number',
                'org_legal_country',
                'org_website',
                'number_of_employees',
                'market_category',
                'annual_fundraising_goal',
                'locale',
                'timezone',
            ]]);
    }

    public function testCanUpdateOrganization(): void
    {
        $this->actingAsAdminUser();

        $site = app(MissionControlService::class)->getSite();

        $data = [
            'org_legal_name' => $this->faker->name,
            'org_legal_address' => $this->faker->address,
            'org_legal_number' => $this->faker->uuid,
            'org_legal_country' => $this->faker->country,
            'org_website' => $this->faker->url,

            'annual_fundraising_goal' => $site->client->annual_fundraising_goal,
            'market_category' => $site->client->market_category,
            'number_of_employees' => $site->client->number_of_employees,

            'locale' => $this->faker->locale,
            'timezone' => $this->faker->timezone,
        ];

        $this->patch(route('api.settings.organization.store'), $data)
            ->assertJson(['data' => $data]);
    }
}
