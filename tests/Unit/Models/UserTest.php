<?php

namespace Tests\Unit\Models;

use Ds\Domain\Shared\Exceptions\PermissionException;
use Ds\Mail\ResetPassword;
use Ds\Mail\SubmitToEmail;
use Ds\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserTest extends TestCase
{
    public function testCanOrRedirectThrowsPermissionExceptionWhenForbidden(): void
    {
        $this->expectException(PermissionException::class);

        $userModel = User::factory()->create();
        $userModel->canOrRedirect('forbidden-permission');
    }

    public function testCanOrRedirectReturnsTrueWhenAllowed(): void
    {
        /** @var \Ds\Models\User */
        $userModelMock = $this->createPartialMock(User::class, ['can']);
        $userModelMock
            ->expects($this->once())
            ->method('can')
            ->willReturn(true);

        $this->assertTrue($userModelMock->canOrRedirect('allowed-permission'));
    }

    public function testNoSendingEmailWhenUserHasNoEmailAddress(): void
    {
        $fakeEmailer = Mail::fake();

        /** @var \Ds\Models\User */
        $user = User::factory()->create(['email' => null]);
        $user->mail(new ResetPassword($user, Str::random(16)));

        $fakeEmailer->assertNothingSent();
        $fakeEmailer->assertNothingQueued();
    }

    public function testSendingEmailWhenUserHasAnEmailAddress(): void
    {
        $fakeEmailer = Mail::fake();

        /** @var \Ds\Models\User */
        $user = User::factory()->create();
        $mailable = new ResetPassword($user, Str::random(16));
        $user->mail($mailable);

        $fakeEmailer->assertSent(ResetPassword::class);
    }

    public function testSendingToAccountAdmins(): void
    {
        Mail::fake();
        Cache::forget(SubmitToEmail::class);

        User::mailAccountAdmins(
            new SubmitToEmail('Test Sending To Account Admins', [])
        );

        Mail::assertSent(SubmitToEmail::class);
    }

    public function testBailOnSendingToAccountAdmins(): void
    {
        Mail::fake();
        Cache::forget(SubmitToEmail::class);

        User::mailAccountAdmins(
            new SubmitToEmail('Test Bail On Sending To Account Admins', []),
            function () {
                return false;
            }
        );

        Mail::assertNotSent(SubmitToEmail::class);
    }
}
