<?php

namespace Tests\Feature\Backend\Api\Settings;

use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

/** @group ApiSettings */
class FundraisingControllerTest extends TestCase
{
    use WithFaker;

    public function testGetReturnsFundraisingResource(): void
    {
        $this->actingAsAdminUser();

        $this->get(route('api.settings.fundraising.show'))
            ->assertJsonStructure(['data' => [
                'org_support_number',
                'org_support_email',
                'org_other_ways_to_donate',
                'org_faq_alternative_question',
                'org_faq_alternative_answer',
                'org_check_mailing_address',
                'org_privacy_officer_email',
                'org_privacy_policy_url',
            ]]);
    }

    public function testCanUpdateFundraising(): void
    {
        $this->actingAsAdminUser();

        $data = [
            'org_support_number' => $this->faker->phoneNumber,
            'org_support_email' => $this->faker->email,
            'org_other_ways_to_donate' => [[
                'label' => $this->faker->text,
                'href' => $this->faker->url,
            ], [
                'label' => $this->faker->text,
                'href' => $this->faker->url,
            ]],
            'org_faq_alternative_question' => $this->faker->sentence,
            'org_faq_alternative_answer' => $this->faker->sentences(3, true),
            'org_check_mailing_address' => $this->faker->address,
            'org_privacy_officer_email' => $this->faker->email,
            'org_privacy_policy_url' => $this->faker->url,
        ];

        $response = $data;

        data_set($response, 'org_other_ways_to_donate.0.id', 1);
        data_set($response, 'org_other_ways_to_donate.1.id', 2);

        $this->patch(route('api.settings.fundraising.store'), $data)
            ->assertJson(['data' => $response]);
    }
}
