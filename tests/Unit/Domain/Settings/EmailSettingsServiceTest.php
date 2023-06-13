<?php

namespace Tests\Unit\Domain\Settings;

use Ds\Domain\Settings\EmailSettingsService;
use Ds\Services\ConfigService;
use Ds\Services\EmailService;
use Exception;
use Tests\TestCase;

/**
 * @group settings
 */
class EmailSettingsServiceTest extends TestCase
{
    public function testValidateEmailFrom(): void
    {
        $this->assertSame(
            'notifications@givecloud.co',
            $this->app->make(EmailSettingsService::class)->validateEmailFrom('notifications@givecloud.co')
        );
    }

    public function testValidateEmailFromWithoutEmail(): void
    {
        $this->assertSame(
            'notifications@givecloud.co',
            $this->app->make(EmailSettingsService::class)->validateEmailFrom(null)
        );
    }

    public function testValidateEmailFromWithInvalidEmailThrowsException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid From address.');

        $this->app->make(EmailSettingsService::class)->validateEmailFrom('invalid @email address');
    }

    public function testValidateEmailFromWithDmarcRejectPolicyThrowsException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unable to send from host.com addresses. They have a DMARC configuration that will reject unauthenticated messages.');

        $emailServiceMock = $this->createMock(EmailService::class);
        $emailServiceMock
            ->expects($this->once())
            ->method('hasDmarcRejectPolicy')
            ->willReturn(true);

        $this->app->make(EmailSettingsService::class, ['emailService' => $emailServiceMock])
            ->validateEmailFrom('valid.email@host.com');
    }

    public function testValidateEmailFromWithSpfRestrictionsThrowsException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unable to send from host.com addresses. They have a SPF configuration that will reject messages from servers that have not been whitelisted.');

        $emailServiceMock = $this->createMock(EmailService::class);
        $emailServiceMock
            ->expects($this->once())
            ->method('hasSpfRestrictions')
            ->willReturn(true);

        $this->app->make(EmailSettingsService::class, ['emailService' => $emailServiceMock])
            ->validateEmailFrom('valid.email@host.com');
    }

    public function testValidateEmailReplyTo(): void
    {
        $this->assertSame(
            'email@gc.test',
            $this->app->make(EmailSettingsService::class)->validateEmailReplyTo('email@gc.test')
        );
    }

    public function testValidateEmailReplyToWithoutEmail(): void
    {
        $this->assertSame('', $this->app->make(EmailSettingsService::class)->validateEmailReplyTo(null));
    }

    public function testValidateEmailReplyToWithInvalidReplyToThrowsException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid Reply-to address.');

        $this->app->make(EmailSettingsService::class)
            ->validateEmailReplyTo('invalid email@address');
    }

    public function testUpdateSuccess(): void
    {
        $name = 'Notifications GC';
        $from = 'notifications@gc.test';
        $replyTo = 'reply@gc.test';

        $this->assertTrue($this->app->make(EmailSettingsService::class)->update($name, $from, $replyTo));
        $this->assertSame($name, sys_get('email_from_name'));
        $this->assertSame($from, sys_get('email_from_address'));
        $this->assertSame($replyTo, sys_get('email_replyto_address'));
    }

    public function testUpdateWhenSavingThrowsException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('There was a problem saving your changes');

        $configServiceMock = $this->createMock(ConfigService::class);
        $configServiceMock
            ->expects($this->once())
            ->method('setKnown')
            ->willReturn(false);

        $name = 'Notifications GC';
        $from = 'notifications@gc.test';
        $replyTo = 'reply@gc.test';
        $this->app->make(EmailSettingsService::class, ['configService' => $configServiceMock])
            ->update($name, $from, $replyTo);

        $this->assertNotSame($name, sys_get('email_from_name'));
        $this->assertNotSame($from, sys_get('email_from_address'));
        $this->assertNotSame($replyTo, sys_get('email_replyto_address'));
    }
}
