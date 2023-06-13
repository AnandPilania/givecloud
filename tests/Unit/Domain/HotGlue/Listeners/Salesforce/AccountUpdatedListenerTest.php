<?php

namespace Tests\Unit\Domain\HotGlue\Listeners\Salesforce;

use Ds\Domain\HotGlue\Listeners\AbstractHandler;
use Ds\Domain\HotGlue\Listeners\Salesforce\AccountUpdated;
use Ds\Events\AccountCreated;
use Ds\Models\Account;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * @group hotglue
 */
class AccountUpdatedListenerTest extends TestCase
{
    public function testShouldQueueReturnsFalseWhenFeatureIsNotActivated(): void
    {
        sys_set('feature_hotglue_salesforce', false);

        $this->assertFalse($this->app->make(AccountUpdated::class)->shouldQueue());
    }

    public function testShouldQueueLooksForConnectedFlow(): void
    {
        Config::set('services.hotglue.salesforce.target_id', 'salesforce_target_id');

        Http::fake(function () {
            return Http::response([[
                'targets' => [
                    'salesforce', // We are looking for `salesforce_target_id`
                    'mailchimp',
                ],
            ]]);
        });

        $this->assertFalse($this->app->make(AccountUpdated::class)->shouldQueue());
    }

    public function testShouldQueueReturnsFalseIfExternalIdIsNotProvided(): void
    {
        $this->mock(AbstractHandler::class)->shouldReceive('shouldQueue')->andReturnTrue();

        sys_set('feature_hotglue_salesforce', true);

        $this->assertEmpty(sys_get('salesforce_contact_external_id'));
        $this->assertFalse($this->app->make(AccountUpdated::class)->shouldQueue());
    }

    public function testShouldQueueReturnsTrueWhenExternalIdIsProvided(): void
    {
        Config::set('services.hotglue.salesforce.target_id', 'salesforce_target_id');

        Http::fake(function () {
            return Http::response([[
                'target' => 'salesforce_target_id',
                'domain' => 'salesforce.com',
                'label' => 'Salesforce',
                'version' => 'v2',
            ]]);
        });

        sys_set('feature_hotglue_salesforce', true);
        sys_set('hotglue_salesforce_linked', true);

        sys_set('salesforce_contact_external_id', 'my_external_id__c');

        $this->assertNotEmpty(sys_get('salesforce_contact_external_id'));
        $this->assertTrue($this->app->make(AccountUpdated::class)->shouldQueue());
    }

    public function testHandleSendsPayload(): void
    {
        Http::fake();

        $account = Account::factory()->create();
        $event = new AccountCreated($account);

        $this->app->make(AccountUpdated::class)->handle($event);

        Http::assertSent(function (Request $request) use ($account) {
            return
                $request['tap'] === 'api' &&
                data_get($request, 'state.Contacts.0.name') === $account->display_name &&
                data_get($request, 'state.Contacts.0.email') === $account->email &&
                data_get($request, 'state.Contacts.0.external_id.value') === $account->hashid;
        });
    }
}
