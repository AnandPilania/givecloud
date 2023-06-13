<?php

namespace Tests\Feature\Console\Commands;

use Tests\StoryBuilder;
use Tests\TestCase;

class NotificationsCommandTest extends TestCase
{
    /**
     * @dataProvider notificationsForExpiringOrExpiredPaymentMethodsProvider
     */
    public function testNotificationsForExpiringOrExpiredPaymentMethods(string $date, string $expectedOutput): void
    {
        StoryBuilder::recurringContribution()
            ->usingCreditCard(['cc_expiry' => '2021-05-31'])
            ->count(2)
            ->create();

        StoryBuilder::recurringContribution()
            ->usingCreditCard(['cc_expiry' => '2021-02-28'])
            ->create();

        StoryBuilder::supporter()
            ->withCreditCard(['cc_expiry' => '2024-05-31'])
            ->count(4)
            ->create();

        $this->artisan('notifications', ['date' => $date])
            ->expectsOutput($expectedOutput)
            ->assertExitCode(0);
    }

    public function notificationsForExpiringOrExpiredPaymentMethodsProvider(): array
    {
        return [
            ['2021-05-01', '2 expiring cards.'],
            ['2021-05-31', '2 expired cards.'],
            ['2021-01-29', '1 expiring cards.'],
            ['2021-02-28', '1 expired cards.'],
            ['2024-05-01', 'No expiring cards.'],
            ['2024-05-31', 'No expired cards.'],
        ];
    }
}
