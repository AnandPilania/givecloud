<?php

namespace Tests\Unit\Domain\Sponsorship\Models;

use Ds\Domain\Sponsorship\Models\Sponsor;
use Ds\Models\Email;
use Tests\TestCase;

/**
 * @group sponsorship
 */
class SponsorTest extends TestCase
{
    public function testNotifySuccess()
    {
        /** @var \Ds\Domain\Sponsorship\Models\Sponsor */
        $sponsor = Sponsor::factory()->create();

        $this->assertTrue($sponsor->notify(Email::factory()->active()->create()));
    }

    public function testNotifyWithoutMemberReturnsFalse()
    {
        /** @var \Ds\Domain\Sponsorship\Models\Sponsor */
        $sponsor = Sponsor::factory()->create([
            'member_id' => 0,
        ]);

        $this->assertFalse($sponsor->notify(Email::factory()->active()->create()));
    }
}
