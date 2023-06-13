<?php

namespace Tests\Feature\Console\Commands\DonorPerfect;

use DomainException;
use Ds\Models\Member as Account;
use Ds\Models\Membership;
use Ds\Services\DonorPerfectService;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Support\Str;
use Tests\TestCase;

class SyncMembershipsCommandTest extends TestCase
{
    public function testWhenDpIsNotEnabled()
    {
        sys_set([
            'dpo_user' => null,
            'dpo_pass' => null,
            'dpo_api_key' => null,
        ]);

        $this->artisan('dp:sync-memberships')->assertExitCode(1);
    }

    public function testUpdatingDonorMembershipInDp()
    {
        sys_set(['dpo_api_key' => Str::random(40)]);

        $dpMembership = Membership::factory()->dpMembership()->create();
        Account::factory(2)->create()->each->addGroup($dpMembership);

        $this->mock(DonorPerfectService::class, function ($mock) {
            $mock->shouldReceive('updateDonorMembership')->twice();
        });

        $this->artisan('dp:sync-memberships')->assertExitCode(0);
    }

    public function testUpdatingDonorMembershipInDpReportsExceptions()
    {
        sys_set(['dpo_api_key' => Str::random(40)]);

        $dpMembership = Membership::factory()->dpMembership()->create();
        Account::factory(1)->create()->each->addGroup($dpMembership);

        $this->mock(DonorPerfectService::class, function ($mock) {
            $mock->shouldReceive('updateDonorMembership')->once()->andThrow(new DomainException);
        });

        $this->mock(ExceptionHandler::class, function ($mock) {
            $mock->shouldReceive('report')->once();
        });

        $this->artisan('dp:sync-memberships')->assertExitCode(0);
    }
}
