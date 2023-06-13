<?php

namespace Ds\Domain\Settings;

use Ds\Domain\QuickStart\Events\QuickStartTaskAffected;
use Ds\Domain\QuickStart\Tasks\CustomEmails;
use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Services\ConfigService;
use Ds\Services\EmailService;
use Illuminate\Support\Str;
use Swift_Validate;

class EmailSettingsService
{
    /** @var \Ds\Services\ConfigService */
    private $configService;

    /** @var \Ds\Services\EmailService */
    private $emailService;

    public function __construct(ConfigService $configService, EmailService $emailService)
    {
        $this->configService = $configService;
        $this->emailService = $emailService;
    }

    /**
     * @throws \Ds\Domain\Shared\Exceptions\MessageException
     */
    public function update(?string $name, ?string $from, ?string $replyTo): bool
    {
        $emailSettingsToSave = [
            'email_from_name' => $name,
            'email_from_address' => $from,
            'email_replyto_address' => $replyTo,
        ];

        if ($this->configService->setKnown($emailSettingsToSave)) {
            QuickStartTaskAffected::dispatch(CustomEmails::initialize());

            return true;
        }

        throw new MessageException('There was a problem saving your changes.');
    }

    /**
     * @throws \Ds\Domain\Shared\Exceptions\MessageException
     */
    public function validateEmailFrom(?string $email): string
    {
        if (empty($email) || $email === 'notifications@givecloud.co') {
            return 'notifications@givecloud.co';
        }

        if (! Swift_Validate::email($email)) {
            throw new MessageException('Invalid From address.');
        }

        $hostname = Str::after($email, '@');

        if ($this->emailService->hasDmarcRejectPolicy($hostname)) {
            throw new MessageException("Unable to send from $hostname addresses. They have a DMARC configuration that will reject unauthenticated messages.");
        }

        if ($this->emailService->hasSpfRestrictions($hostname)) {
            throw new MessageException("Unable to send from $hostname addresses. They have a SPF configuration that will reject messages from servers that have not been whitelisted.");
        }

        return $email;
    }

    /**
     * @throws \Ds\Domain\Shared\Exceptions\MessageException
     */
    public function validateEmailReplyTo(?string $email = ''): string
    {
        if (! empty($email) && ! Swift_Validate::email($email)) {
            throw new MessageException('Invalid Reply-to address.');
        }

        return (string) $email;
    }
}
