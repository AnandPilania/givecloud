<?php

namespace Tests\Unit\Domain\HotGlue\Listeners\Mailchimp;

use Ds\Domain\HotGlue\Listeners\Mailchimp\MemberOptinChanged;
use Ds\Events\MemberOptinChanged as MemberOptinChangedEvent;
use Ds\Models\Account;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * @group hotglue
 */
class AccountUpdatedListenerTest extends TestCase
{
    public function testShouldQueueReturnsFalseWhenFeatureIsNotActivated(): void
    {
        sys_set('feature_hotglue_mailchimp', false);

        $this->assertFalse($this->app->make(MemberOptinChanged::class)->shouldQueue());
    }

    public function testShouldQueueReturnsFalseWhenNotLinked(): void
    {
        sys_set('feature_hotglue_mailchimp', true);
        sys_set('hotglue_mailchimp_linked', false);

        $this->assertFalse($this->app->make(MemberOptinChanged::class)->shouldQueue());
    }

    public function testShouldQueueLooksForConnectedFlow(): void
    {
        Http::fake(function () {
            return Http::response([[
                'target' => 'mailchimp_target_id', // null in config
                'domain' => 'mailchimp.com',
                'label' => 'Mailchimp',
                'version' => 'v2',
            ]]);
        });

        sys_set('feature_hotglue_mailchimp', true);
        sys_set('hotglue_mailchimp_linked', true);

        $this->assertFalse($this->app->make(MemberOptinChanged::class)->shouldQueue());
    }

    public function testHandleSendsPayload(): void
    {
        Http::fake();

        $account = Account::factory()->create();
        $event = new MemberOptinChangedEvent($account);

        $this->app->make(MemberOptinChanged::class)->handle($event);

        Http::assertSent(function (Request $request) use ($account) {
            return
                $request['tap'] === 'api'
                && data_get($request, 'state.Customers.0.name') === $account->display_name
                && data_get($request, 'state.Customers.0.email') === $account->email
                && data_get($request, 'state.Customers.0.subscribe_status') === 'unsubscribed';
        });
    }
}
