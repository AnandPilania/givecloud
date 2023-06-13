<?php

namespace Tests\Feature\Frontend\API\Account;

use Ds\Enums\MemberOptinAction;
use Ds\Models\Member;
use Ds\Models\MemberOptinLog;
use Tests\TestCase;

class AccountControllerTest extends TestCase
{
    public function testMemberCanUpdateTheirEmail(): void
    {
        // Enabled only edit-profile account feature
        sys_set('account_login_features', ['edit-profile']);

        $account = Member::factory()->individual()->create();
        $newEmail = 'testing@givecloud.test';

        $this
            ->actingAsAccount($account)
            ->patchJson(route('api.account.account.update_account'), ['email' => $newEmail])
            ->assertOk()
            ->assertJson([
                'account' => [
                    'id' => $account->getKey(),
                    'email' => $newEmail,
                ],
            ]);
    }

    public function testMemberCannotUpdateTheirEmail(): void
    {
        // Disabled all account features
        sys_set('account_login_features', null);

        $account = Member::factory()->individual()->create();
        $newEmail = 'testing@givecloud.test';

        $this
            ->actingAsAccount($account)
            ->patchJson(route('api.account.account.update_account'), ['email' => $newEmail])
            ->assertOk()
            ->assertJsonMissing([
                'account' => [
                    'id' => $account->getKey(),
                    'email' => $newEmail,
                ],
            ]);
    }

    public function testNoAttributesAreRequiredWhenNotPresent(): void
    {
        sys_set('account_login_features', ['edit-profile']);

        $account = Member::factory()->individual()->create();

        $this
            ->actingAsAccount($account)
            ->patchJson(route('api.account.account.update_account'), [])
            ->assertOk();
    }

    public function testUpdateAccountWithMapping(): void
    {
        $account = Member::factory()->individual()->create();
        $city = 'Sant Cugat';

        $this
            ->actingAsAccount($account)
            ->patchJson(route('api.account.account.update_account'), ['billing_city' => $city])
            ->assertOk()
            ->assertJson([
                'account' => [
                    'id' => $account->getKey(),
                    'billing_address' => [
                        'city' => $city,
                    ],
                ],
            ]);
    }

    public function testUpdateAccountWithOptin(): void
    {
        $account = Member::factory()->individual()->create();

        $this
            ->actingAsAccount($account)
            ->patchJson(route('api.account.account.update_account'), ['email_opt_in' => true])
            ->assertOk()
            ->assertJson([
                'account' => [
                    'id' => $account->getKey(),
                    'email_opt_in' => true,
                    'last_optin_log' => [
                        'action' => MemberOptinAction::OPTIN,
                    ],
                ],
            ]);
    }

    public function testUpdateAccountWithOptout(): void
    {
        $optin = MemberOptinLog::factory()->optin()->make();
        $account = Member::factory()->individual()->create();
        $account->optinLogs()->save($optin);
        $optOutReason = 'some reason';

        $this
            ->actingAsAccount($account)
            ->patchJson(route('api.account.account.update_account'), [
                'email_opt_in' => false,
                'email_opt_out_reason' => $optOutReason,
            ])->assertOk()
            ->assertJson([
                'account' => [
                    'id' => $account->getKey(),
                    'email_opt_in' => false,
                    'last_optin_log' => [
                        'action' => MemberOptinAction::OPTOUT,
                        'reason' => $optOutReason,
                    ],
                ],
            ]);
    }

    public function testUpdateAccountWithOptoutOtherReason(): void
    {
        $optin = MemberOptinLog::factory()->optin()->make();
        $account = Member::factory()->individual()->create();
        $account->optinLogs()->save($optin);
        $optOutReason = 'some other reason';

        $this
            ->actingAsAccount($account)
            ->patchJson(route('api.account.account.update_account'), [
                'email_opt_in' => false,
                'email_opt_out_reason' => 'other',
                'email_opt_out_reason_other' => $optOutReason,
            ])->assertOk()
            ->assertJson([
                'account' => [
                    'id' => $account->getKey(),
                    'email_opt_in' => false,
                    'last_optin_log' => [
                        'action' => MemberOptinAction::OPTOUT,
                        'reason' => $optOutReason,
                    ],
                ],
            ]);
    }
}
