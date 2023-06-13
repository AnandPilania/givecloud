<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drop;
use Ds\Enums\SocialLogin\SupporterProviders;
use Ds\Models\GroupAccountTimespan;
use Ds\Models\Member;
use Ds\Models\MemberOptinLog;
use Ds\Models\Order;
use Ds\Models\OrderItemFile;
use Ds\Repositories\MemberOptinLogRepository;

class AccountDrop extends Drop
{
    /** @var array */
    protected $serializationBlacklist = ['purchased_media'];

    protected $attributes = [
        'id',
        'avatar',
        'display_name',
        'title',
        'first_name',
        'last_name',
        'email',
        'email_opt_in',
        'nps',
        'is_denied',
        'is_pending',
        'is_unverified',
    ];

    protected function initialize($source)
    {
        $this->liquid = [
            'organization_name' => $source->bill_organization_name,
            'account_type' => $source->accountType,
            'password_expired' => $source->force_password_reset,
            'lifetime_donation_amount' => $source->lifetime_donation_amount,
            'lifetime_donation_count' => $source->lifetime_donation_count,
            'lifetime_purchase_amount' => $source->lifetime_purchase_amount,
            'lifetime_purchase_count' => $source->lifetime_purchase_count,
            'lifetime_fundraising_amount' => $source->lifetime_fundraising_amount,
            'lifetime_fundraising_count' => $source->lifetime_fundraising_count,
            'referral_link' => $source->getShareableLink('/'),
        ];
    }

    public function billing_address()
    {
        return new AddressDrop($this->source, 'billing');
    }

    public function shipping_address()
    {
        return new AddressDrop($this->source, 'shipping');
    }

    public function can_revoke_social_identity(): bool
    {
        return $this->has_password()
            || $this->source->socialIdentities()->count() > 1;
    }

    public function has_password(): bool
    {
        $this->source->makeVisible('password');

        return ! is_null($this->source->password);
    }

    public function membership_badge()
    {
        return GroupAccountTimespan::badges()
            ->where('account_id', $this->source->id)
            ->get()
            ->sortByDesc(function ($group) {
                return [$group->is_active, $group->start_date];
            })->first();
    }

    public function memberships()
    {
        return GroupAccountTimespan::query()
            ->where('account_id', $this->source->id)
            ->get()
            ->groupBy('group_id')
            ->map(function ($groups) {
                return $groups->sortByDesc(function ($group) {
                    return [$group->is_active, $group->start_date];
                })->first();
            });
    }

    public function notices()
    {
        return $this->source->notices ?? null;
    }

    public function payment_methods()
    {
        return $this->source->paymentMethods()
            ->where('status', 'ACTIVE')
            ->orderBy('use_as_default', 'desc')
            ->get();
    }

    public function payment_method_count()
    {
        return $this->source->paymentMethods()->where('status', 'ACTIVE')->count();
    }

    public function pledges()
    {
        return $this->source->pledges;
    }

    public function social_identities()
    {
        return $this->source->socialIdentities()->get()->keyBy('provider_name');
    }

    /**
     * Returns all providers, sorted by connected ones first.
     */
    public function social_login_providers()
    {
        return array_unique(array_merge(
            $this->social_identities()->keys()->toArray(),
            SupporterProviders::cases(),
        ));
    }

    public function subscriptions()
    {
        return $this->source->recurringPaymentProfiles()
            ->orderByRaw("FIELD(status,'SUSPENDED','ACTIVE','EXPIRED','CANCELLED') DESC, profile_start_date DESC")->get();
    }

    public function subscription_count()
    {
        return $this->source->recurringPaymentProfiles()->active()->count();
    }

    public function sponsorships()
    {
        return $this->source->sponsors()->active()->get();
    }

    public function sponsorship_count()
    {
        return $this->source->sponsors()->active()->count();
    }

    public function tax_receipts()
    {
        return $this->source->taxReceipts;
    }

    public function tax_receipt_count()
    {
        return $this->source->taxReceipts->count();
    }

    public function last_payment()
    {
        return $this->source->lastPayment;
    }

    public function external()
    {
        return new AccountExternalDrop($this->source);
    }

    public function purchased_media()
    {
        return OrderItemFile::query()
            ->with('item.order', 'item.variant.product')
            ->select('productorderitemfiles.*')
            ->join('productorderitem', 'productorderitem.id', '=', 'productorderitemfiles.orderitemid')
            ->join('productorder', 'productorder.id', '=', 'productorderitem.productorderid')
            ->whereNull('productorder.deleted_at')
            ->where('productorder.member_id', $this->source->id)
            ->orderBy('productorder.ordered_at', 'desc')
            ->get();
    }

    public function first_donation_date()
    {
        return $this->source->firstOrder->confirmationdatetime ?? null;
    }

    public function active_recurring_donations()
    {
        return $this->source->activeRecurringPaymentProfiles->count();
    }

    public function secondary_impact_donations_amount()
    {
        return Member::where('referred_by', $this->source->id)->sum('lifetime_donation_amount');
    }

    public function secondary_impact_donation_count()
    {
        return Member::where('referred_by', $this->source->id)->sum('lifetime_donation_count');
    }

    public function secondary_impact_site_visit_count()
    {
        return Order::where('referred_by', $this->source->id)->count();
    }

    public function secondary_impact_email_sign_up_count()
    {
        return Order::where('referred_by', $this->source->id)->where('email_opt_in', 1)->count();
    }

    public function last_optin_log(): ?MemberOptinLog
    {
        return app(MemberOptinLogRepository::class)->getLastLogFromMember($this->source->getKey());
    }
}
