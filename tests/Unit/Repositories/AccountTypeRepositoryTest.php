<?php

namespace Tests\Unit\Repositories;

use Ds\Repositories\AccountTypeRepository;
use Tests\TestCase;

class AccountTypeRepositoryTest extends TestCase
{
    public function testOnWebAccountTypeDropsAllowOnWebEnabledByDefault(): void
    {
        $accountTypes = app(AccountTypeRepository::class)->getOnWebAccountTypeDrops();

        $this->assertCount(2, $accountTypes);
    }

    public function testOnWebAccountTypeDropsWhenAllowOnWebDisabled(): void
    {
        sys_set(['allow_account_types_on_web' => false]);

        $accountTypes = app(AccountTypeRepository::class)->getOnWebAccountTypeDrops();

        $this->assertCount(0, $accountTypes);
    }
}
