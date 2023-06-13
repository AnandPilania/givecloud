<?php

namespace Tests\Browser\Themes\Social\SecondaryImpact;

use Ds\Models\Member;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ReferralCookieTest extends DuskTestCase
{
    /**
     * Ensure cookie is not set for an invalid code.
     *
     * @return void
     */
    public function testIngoreInvalidCode()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/?gcr=invalidCode')
                ->assertCookieMissing('gcr');
        });
    }

    /**
     * Ensure a valid cookie is not updated by and invalid code.
     *
     * @return void
     */
    public function testInvalidCodeShouldNotReplaceValidCode()
    {
        $member = Member::factory()->create();

        $this->browse(function (Browser $browser) use ($member) {
            $browser->visit("/?gcr={$member->referral_code}")
                ->assertCookieValue('gcr', $member->id)
                ->visit('/?gcr=invalidCode')
                ->assertCookieValue('gcr', $member->id);
        });
    }
}
