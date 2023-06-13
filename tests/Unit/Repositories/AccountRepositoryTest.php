<?php

namespace Tests\Unit\Repositories;

use Ds\Models\Member as Account;
use Ds\Models\Membership;
use Ds\Repositories\AccountRepository;
use Tests\TestCase;

class AccountRepositoryTest extends TestCase
{
    public function testCountingAccountsWithDpMembershipStartingToday(): void
    {
        $this->generateDataForDpMembershipStartingTodayTests();

        $accountsWithDpMembershipStartingToday = $this->app->make(AccountRepository::class)->countAccountsWithDpMembershipStartingToday();

        $this->assertSame(2, $accountsWithDpMembershipStartingToday);
    }

    public function testChunkingAccountsWithDpMembershipStartingToday(): void
    {
        $this->generateDataForDpMembershipStartingTodayTests();

        $foundRecords = false;

        $this->app->make(AccountRepository::class)->chunkAccountsWithDpMembershipStartingToday(250, function (iterable $records) use (&$foundRecords) {
            $this->assertCount(2, $records);
            $foundRecords = true;
        });

        $this->assertTrue($foundRecords);
    }

    private function generateDataForDpMembershipStartingTodayTests(): void
    {
        $nonDpMembership = Membership::factory()->create();
        Account::factory(2)->create()->each->addGroup($nonDpMembership);
        Account::factory(3)->create()->each->addGroup($nonDpMembership, '-1 month');

        $dpMembership = Membership::factory()->dpMembership()->create();
        Account::factory(2)->create()->each->addGroup($dpMembership);
        Account::factory(3)->create()->each->addGroup($dpMembership, '-1 month');
    }
}
