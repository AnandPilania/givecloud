<?php

namespace Ds\Providers;

use Ds\Listeners\User\LogSuccessfulLogin;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as IlluminateServiceProvider;

class EventServiceProvider extends IlluminateServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        \Ds\Events\AccountCreated::class => [
            \Ds\Listeners\Member\PushInfusionsoftContact::class,
        ],
        \Ds\Events\AccountAddedToGroup::class => [
            \Ds\Listeners\Member\PushInfusionsoftTag::class,
        ],
        \Ds\Events\AccountWasRegistered::class => [
            \Ds\Listeners\Member\NotifyNewAccount::class,
        ],
        \Ds\Events\PledgeCreated::class => [
            \Ds\Listeners\Pledge\TrackPledgableAmount::class,
        ],
        \Ds\Events\PledgeDeleted::class => [
            \Ds\Listeners\Pledge\RollbackPledgableAmount::class,
        ],
        \Ds\Events\ProductWasPurchased::class => [
            \Ds\Listeners\OrderItem\UpdateFundraisingAggregates::class,
            \Ds\Listeners\Product\CalculatePledges::class,
            \Ds\Listeners\Product\TrackPledgableAmounts::class,
        ],
        \Ds\Events\ProductWasRefunded::class => [
            \Ds\Listeners\Product\RollbackPledgableAmounts::class,
        ],
        \Ds\Events\OrderWasCompleted::class => [
            \Ds\Listeners\Order\FlagTestOrders::class,
            \Ds\Listeners\Order\StockAdjustments::class,
            \Ds\Listeners\Order\IncrementPromoCodes::class,
            \Ds\Listeners\Order\ApplyMemberships::class,
            \Ds\Listeners\Order\CreateSponsors::class,
            \Ds\Listeners\Order\CreateTributes::class,
            \Ds\Listeners\Order\IssueTaxReciept::class,
            \Ds\Listeners\Order\TaxCloudCapture::class,
            \Ds\Listeners\Order\UpdateEmailOptIn::class,
            \Ds\Listeners\Order\SendNotificationEmails::class,
            \Ds\Listeners\Order\TrackInGoogleAnalytics::class,
            \Ds\Listeners\Order\DonorPerfectSync::class,
            \Ds\Listeners\Member\CalculateLifetimeGiving::class,
            \Ds\Domain\Webhook\Listeners\OrderCompleted::class,
            \Ds\Listeners\Order\ProductPurchases::class,
            \Ds\Listeners\Order\UpdateLedgerEntries::class,
            \Ds\Listeners\Supporter\UpdateLastPaymentDate::class,
            \Ds\Listeners\Supporter\UpdateSupporterLocalization::class,
            \Ds\Domain\QuickStart\Listeners\PaymentOccurredListener::class,
        ],
        \Ds\Events\OrderWasRefunded::class => [
            \Ds\Listeners\Order\PushRefundToDonorPerfect::class,
            \Ds\Listeners\Order\UpdateLedgerEntries::class,
            \Ds\Listeners\Order\UpdateFundraisingAggregates::class,
            \Ds\Listeners\Member\CalculateLifetimeGiving::class,
        ],
        \Ds\Events\RecurringBatchCompleted::class => [
            //
        ],
        \Ds\Events\RecurringPaymentWasCompleted::class => [
            \Ds\Listeners\Member\CalculateLifetimeGiving::class,
            \Ds\Listeners\Member\ExtendMembership::class,
            \Ds\Listeners\Supporter\UpdateLastPaymentDate::class,
            \Ds\Listeners\Transactions\CalculatePledges::class,
            \Ds\Listeners\Transactions\UpdateLedgerEntries::class,
            \Ds\Listeners\Transactions\DonorPerfectSync::class,
            \Ds\Domain\QuickStart\Listeners\PaymentOccurredListener::class,
        ],
        \Ds\Events\RecurringPaymentWasRefunded::class => [
            \Ds\Listeners\Transactions\CalculatePledges::class,
            \Ds\Listeners\Transactions\UpdateLedgerEntries::class,
            \Ds\Listeners\Transactions\UpdateFundraisingAggregates::class,
            \Ds\Listeners\Member\CalculateLifetimeGiving::class,
        ],
        \Ds\Domain\Sponsorship\Events\SponsorWasStarted::class => [
            \Ds\Domain\Sponsorship\Listeners\UpdateSponsorCount::class,
            \Ds\Domain\Sponsorship\Listeners\NotifySponsorshipStart::class,
        ],
        \Ds\Domain\Sponsorship\Events\SponsorWasEnded::class => [
            \Ds\Domain\Sponsorship\Listeners\UpdateSponsorCount::class,
            \Ds\Domain\Sponsorship\Listeners\SuspendRecurringPaymentProfile::class,
            \Ds\Domain\Sponsorship\Listeners\NotifySponsorshipEnd::class,
        ],
        \Ds\Events\MediaUploaded::class => [
            \Ds\Listeners\AutoTagImage::class,
        ],
        \Illuminate\Queue\Events\JobExceptionOccurred::class => [
            \Ds\Listeners\LogJobException::class,
        ],
        \Ds\Events\UserCreated::class => [
            \Ds\Domain\MissionControl\Listeners\UpdateMissionControlSiteUsers::class,
        ],
        \Ds\Events\UserWasUpdated::class => [
            \Ds\Domain\MissionControl\Listeners\UpdateMissionControlSiteUsers::class,
        ],
        \Ds\Events\MemberOptinChanged::class => [
            \Ds\Listeners\Member\PushDonorPerfectOptin::class,
            \Ds\Listeners\Member\PushInfusionsoftOptin::class,
        ],
        \SocialiteProviders\Manager\SocialiteWasCalled::class => [
            \SocialiteProviders\Facebook\FacebookExtendSocialite::class . '@handle',
            \SocialiteProviders\Google\GoogleExtendSocialite::class . '@handle',
            \SocialiteProviders\Microsoft\MicrosoftExtendSocialite::class . '@handle',
        ],

        \Ds\Domain\QuickStart\Events\QuickStartTaskAffected::class => [
            \Ds\Domain\QuickStart\Listeners\QuickStartTaskAffectedListener::class,
        ],
        Login::class => [
            LogSuccessfulLogin::class,
        ],
    ];

    /**
     * List of additional EventServiceProviders to register.
     */
    protected $domainEventServiceProviders = [
        \Ds\Domain\Webhook\HookEventServiceProvider::class,
        \Ds\Domain\Zapier\ZapierEventServiceProvider::class,
        \Ds\Domain\Salesforce\SalesforceEventServiceProvider::class,
        \Ds\Domain\HotGlue\HotGlueEventServiceProvider::class,
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }

    /**
     * Get the events and handlers.
     *
     * @return array
     */
    public function listens()
    {
        return $this->getAllEventListeners($this->listen, $this->domainEventServiceProviders);
    }

    /**
     *  Merge all additional EventServiceProviders into a new array.
     */
    protected function getAllEventListeners(array $listeners = [], array $extraServiceProdiverClassNames = []): array
    {
        foreach ($extraServiceProdiverClassNames as $domainProvider) {
            // Verify that the provider implements DomainEventServiceProviderInterface
            if (in_array(DomainEventServiceProviderInterface::class, class_implements($domainProvider), true)) {
                $listeners = array_merge_recursive($listeners, $domainProvider::listens());
            }
        }

        return $listeners;
    }
}
