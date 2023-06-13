<?php

namespace Tests\Unit\Domain\HotGlue\Listeners\HubSpot;

use Ds\Domain\HotGlue\Listeners\HubSpot\AccountUpdated;
use Ds\Events\AccountCreated;
use Ds\Models\Account;
use Tests\TestCase;

/**
 * @group hotglue
 */
class AccountUpdatedTest extends TestCase
{
    public function testStateReturnsAccountTransformerState(): void
    {
        $account = Account::factory()->create();
        $event = new AccountCreated($account);

        $data = $this->app->make(AccountUpdated::class)->state($event);

        $this->assertSame($account->display_name, $data['Contacts'][0]['name']);
    }
}
