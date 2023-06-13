<?php

namespace Tests\Unit\Mail;

use Ds\Mail\SocialLoginNewProviderDetected;
use Ds\Models\Member;
use Tests\Concerns\InteractsWithMailables;
use Tests\TestCase;

class SocialLoginNewProviderDetectedTest extends TestCase
{
    use InteractsWithMailables;

    public function testMailablePreview(): void
    {
        $mailable = new SocialLoginNewProviderDetected(Member::factory()->create(), 'google');

        $this->assertMailablePreview($mailable);
    }
}
