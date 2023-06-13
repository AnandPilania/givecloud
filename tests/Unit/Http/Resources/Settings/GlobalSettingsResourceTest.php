<?php

namespace Tests\Unit\Http\Resources\Settings;

use Ds\Http\Resources\Settings\GlobalSettingsResource;
use Illuminate\Http\Request;
use Tests\TestCase;

/** @group ApiSettings */
class GlobalSettingsResourceTest extends TestCase
{
    public function testToArrayReturnsAllKeys(): void
    {
        $settings = GlobalSettingsResource::make()->toArray(new Request());

        $this->assertArrayHasKey('org_logo', $settings);
        $this->assertArrayHasKey('org_primary_color', $settings);
        $this->assertArrayHasKey('org_legal_name', $settings);
        $this->assertArrayHasKey('org_legal_address', $settings);
        $this->assertArrayHasKey('org_legal_country', $settings);
        $this->assertArrayHasKey('org_legal_number', $settings);
        $this->assertArrayHasKey('org_check_mailing_address', $settings);
        $this->assertArrayHasKey('org_support_number', $settings);
        $this->assertArrayHasKey('org_support_email', $settings);
        $this->assertArrayHasKey('org_other_ways_to_donate', $settings);
        $this->assertArrayHasKey('org_privacy_officer_email', $settings);
        $this->assertArrayHasKey('org_privacy_policy_url', $settings);
        $this->assertArrayHasKey('org_website', $settings);
    }
}
