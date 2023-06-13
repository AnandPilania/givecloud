<?php

namespace Tests\Unit\Mail;

use Ds\Mail\ResetPassword;
use Ds\Models\User;
use Illuminate\Support\Str;
use Tests\Concerns\InteractsWithMailables;
use Tests\TestCase;

class ResetPasswordTest extends TestCase
{
    use InteractsWithMailables;

    public function testMailablePreview(): void
    {
        $mailable = new ResetPassword(
            User::factory()->create(),
            Str::random(24),
        );

        $this->assertMailablePreview($mailable);
    }
}
