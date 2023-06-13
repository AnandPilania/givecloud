<?php

namespace Tests\Unit\Services;

use Ds\Services\EmailService;
use Tests\TestCase;

class EmailServiceTest extends TestCase
{
    public function testGetsValidEmailsFromStringList()
    {
        $emailService = $this->app->make(EmailService::class);

        $emails = 'philippe@givecloud.com, someother@gmail.com,    whitespaced@outlook.com   , invalid-email@ , @another ';

        $validEmails = $emailService->getValidEmailsFromString($emails);

        $this->assertIsArray($validEmails);
        $this->assertCount(3, $validEmails);

        $this->assertContains('philippe@givecloud.com', $validEmails);
        $this->assertContains('someother@gmail.com', $validEmails);
        $this->assertContains('whitespaced@outlook.com', $validEmails);
        $this->assertNotContains('invalid-email@', $validEmails);
    }

    /**
     * @dataProvider fishyEmailDataProvider
     */
    public function testFishyValuesReturnsArray($email)
    {
        $emailService = $this->app->make(EmailService::class);

        $this->assertSame([], $emailService->getValidEmailsFromString($email));
    }

    public function fishyEmailDataProvider()
    {
        return [
            [null],
            ['invalid-email@'],
            [' @another@invalid@givecloud.com'],
            ['1212'],
        ];
    }
}
