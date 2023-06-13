<?php

namespace Ds\Domain\MissionControl;

use Carbon\Carbon;
use Ds\Domain\MissionControl\Models\Site;
use Ds\Domain\Shared\Exceptions\HtmlableException;
use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Models\User;
use Illuminate\Cache\CacheManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use IPLib\Factory as IPLib;
use Swift_Message;

class MissionControlService
{
    /** @var \Illuminate\Cache\CacheManager */
    private $cache;

    /**
     * @param \Illuminate\Cache\CacheManager $cache
     */
    public function __construct(CacheManager $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Get the site.
     *
     * WARNING: It is very important that this method NEVER ask
     * for any settings other than `ds_account_name`. Doing so
     * will trigger a recursive loop while trying to determine
     * the database name for the site.
     */
    public function getSite(): Site
    {
        return reqcache('sys-backend:site', function () {
            $site = $this->getSiteFromCache();

            if (empty($site)) {
                if (App::runningInConsole()) {
                    throw new MessageException('Unable to determine site');
                }

                throw new HtmlableException(view('errors.maintenance'));
            }

            return $site;
        });
    }

    private function getSiteFromCache(): ?Site
    {
        $key = 'sys-backend:get-site:' . sys_get('ds_account_name');

        return $this->cache->store('app')->rememberForever($key, function () {
            $site = DB::connection('sys-backend')->table('sites')
                ->where('status', 'ACTIVE')
                ->where('ds_account_name', sys_get('ds_account_name'))
                ->first();

            if (empty($site)) {
                return null;
            }

            $site->client = DB::connection('sys-backend')->table('clients')
                ->where('id', $site->client_id)
                ->first();

            $site->partner = DB::connection('sys-backend')->table('resellers')
                ->where('id', $site->reseller_id)
                ->first();

            $site->domains = DB::connection('sys-backend')->table('site_domains')
                ->where('site_id', $site->id)
                ->get()
                ->toArray();

            $site->subscription = DB::connection('sys-backend')->table('subscriptions')
                ->where('client_id', $site->client_id)
                ->whereNull('deleted_at')
                ->orderByDesc('id')
                ->first();

            $site->plan = DB::connection('sys-backend')->table('plans')
                ->where('id', $site->subscription->plan_id)
                ->first();

            return new Site((array) $site);
        });
    }

    public function flushSiteCache(): void
    {
        $key = 'sys-backend:get-site:' . sys_get('ds_account_name');

        $this->cache->store('app')->flush($key);
    }

    /**
     * Get the authoritative site.
     *
     * @return \Ds\Domain\MissionControl\Models\Site|null
     */
    public function getAuthoritativeSite(): ?Site
    {
        $dsAccountName = sys_get('sponsorship_database_name');

        if (empty($dsAccountName)) {
            return null;
        }

        return $this->cache->store()->remember(
            "sys-backend:authoritative-site:$dsAccountName",
            now()->addHour(1),
            function () use ($dsAccountName) {
                $site = DB::connection('sys-backend')->table('sites')
                    ->where('ds_account_name', $dsAccountName)
                    ->first();

                if (empty($site)) {
                    return null;
                }

                return new Site((array) $site);
            }
        );
    }

    /**
     * Get the backend url.
     *
     * @return string
     * @static
     */
    public static function getMissionControlUrl(): string
    {
        return 'https://' . config('givecloud.missioncontrol_domain');
    }

    /**
     * Get the backend api url.
     *
     * @return string
     * @static
     */
    public static function getMissionControlApiUrl($path = ''): string
    {
        return self::getMissionControlUrl() . '/api/v1/' . $path;
    }

    /**
     * Check if domain is already in use by another site.
     *
     * @return bool
     */
    public function isDomainAlreadyInUse($domain): bool
    {
        $domains = DB::connection('sys-backend')->table('site_domains')
            ->where('site_id', '!=', $this->getSite()->id)
            ->where('name', $domain)
            ->count();

        return (bool) $domains;
    }

    public function isBlockedIp(?string $ip = null, array $lists = null): bool
    {
        $ip = IPLib::parseAddressString($ip ?? request()->ip());

        if (empty($ip)) {
            return false;
        }

        if ($ip->getRangeType() !== \IPLib\Range\Type::T_PUBLIC) {
            return false;
        }

        return DB::connection('sys-backend')->table('blocklist_ranges')
            ->when($lists, fn ($query) => $query->whereIn('list', $lists))
            ->where('address_type', $ip->getAddressType())
            ->whereRaw('? between range_from and range_to', [$ip->getComparableString()])
            ->exists();
    }

    /**
     * Get in app updates.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getInAppUpdates(): Collection
    {
        $updates = $this->cache->store('app')->rememberForever(
            'sys-backend:in-app-updates',
            function () {
                return DB::connection('sys-backend')->table('updates')
                    ->where('in_app', 1)
                    ->where('is_live', 1)
                    ->orderBy('posted_at', 'desc')
                    ->orderBy('created_at', 'desc')
                    ->get();
            }
        );

        return $updates->map(function ($update) {
            $update->is_new = ! user()->last_opened_updates_feed_at || user()->last_opened_updates_feed_at->lt(new Carbon($update->created_at));

            return $update;
        });
    }

    /**
     * Get notices.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getNotices(): Collection
    {
        return $this->cache->store('app')->remember(
            'sys-backend:notices',
            now()->addHours(12),
            function () {
                return DB::connection('sys-backend')->table('notices')
                    ->where(function ($query) {
                        $query->where('start_at', '<=', now());
                        $query->where('end_at', '>=', now());
                        $query->where('version', $this->getSite()->version);
                    })->orderBy('start_at', 'desc')
                    ->get();
            }
        );
    }

    /**
     * Get invoices.
     *
     * @return object|null
     */
    public function getInvoice($invoiceId)
    {
        $invoice = DB::connection('sys-backend')->table('invoices')
            ->where('client_id', $this->getSite()->client_id)
            ->where('id', $invoiceId)
            ->where('is_draft', 0)
            ->first();

        if (empty($invoice)) {
            return null;
        }

        $invoice->client = $this->getSite()->client;

        $invoice->items = DB::connection('sys-backend')->table('invoice_items')
            ->where('invoice_id', $invoice->id)
            ->get();

        $invoice->number = sprintf(
            'GC%s-%s',
            fromUtcFormat($invoice->invoiced_at, 'y'),
            str_pad($invoice->id, 4, '0', STR_PAD_LEFT)
        );

        return $invoice;
    }

    /**
     * Get list of market groups.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getMarketGroups(): Collection
    {
        return collect([
            'Animal & Wildlife' => [
                'Hunting & Fishing Conservation',
                'Museums, Zoos & Aquariums',
                'Pet & Animal Welfare',
                'Wildlife Conservation',
                'Other',
            ],
            'Arts & Culture' => [
                'Museums & Art Galleries',
                'Performing Arts',
                'Libraries & Historical Societies',
                'Public Broadcasting & Media',
                'Other',
            ],
            'Education' => [
                'Elementary, Jr High and High Schools',
                'University & College',
                'Scholarship & Financial Aid',
                'School Reform and Experimental Education',
                'Support for Students, Teachers and Parents',
                'Other',
            ],
            'Environment' => [
                'Parks & Nature Centers',
                'Conservation & Protection',
                'Other',
            ],
            'Faith-Based / Religious' => [
                'Worship Center / Church',
                'Media & Broadcasting',
                'Other',
            ],
            'Health' => [
                'Disease & Disorders',
                'Medical Services & Treatment',
                'Medical Research',
                'Patient & Family Support',
                'Other',
            ],
            'Human Justice' => [
                'Child Sponsorship',
                'Disaster Relief',
                'International Development',
                'Human Trafficking',
                'Other',
            ],
            'Social & Community' => [
                'Homeless Services',
                'Social Services',
                'Food Bank, Pantry or Distribution Services',
                'Youth Development, Shelter, and Crisis Services',
                'Other',
            ],
        ]);
    }

    public function getPlans(): Collection
    {
        return $this->cache->store('app')->remember(
            'sys-backend:plans',
            now()->addDays(7),
            function () {
                return DB::connection('sys-backend')->table('plans')->get();
            }
        );
    }

    public function setSubscription(array $attributes): bool
    {
        $res = DB::connection('sys-backend')->table('subscriptions')
            ->insert($attributes);

        $this->flushSiteCache();

        return $res;
    }

    public function setTimezone(string $timezone, bool $flushSiteCache = true): void
    {
        $this->updateSite(['timezone' => $timezone]);

        if ($flushSiteCache) {
            $this->flushSiteCache();
        }
    }

    /**
     * Updating the site custom domains.
     *
     * @param array $domains
     */
    public function updateCustomDomains(array $domains)
    {
        $site = $this->getSite();

        $domains = collect($domains)->reject(function ($domain) use ($site) {
            return $site->domains->where('name', $domain)->isNotEmpty();
        });

        if ($domains->isEmpty()) {
            return;
        }

        $domains = $domains->map(function ($domain) use ($site) {
            return [
                'site_id' => $site->id,
                'status' => 'AWAITING_VERIFICATION',
                'name' => $domain,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        });

        DB::connection('sys-backend')->table('site_domains')
            ->insert($domains->values()->toArray());

        $site->domains = DB::connection('sys-backend')->table('site_domains')
            ->where('site_id', $site->id)
            ->get()
            ->toArray();

        $this->flushSiteCache();
    }

    /**
     * Updating the client.
     *
     * @param array $attributes
     */
    public function updateClient(array $attributes)
    {
        $attributes = array_merge($attributes, [
            'updated_at' => now()->toDatetimeFormat(),
        ]);

        DB::connection('sys-backend')->table('clients')
            ->where('id', $this->getSite()->client_id)
            ->update($attributes);

        $this->flushSiteCache();
    }

    public function updateResellerId(int $resellerId = 1): void
    {
        $this->updateBilling(['reseller_id' => $resellerId]);
        $this->updateClient(['reseller_id' => $resellerId]);
        $this->updateContacts(['reseller_id' => $resellerId]);
        $this->updateInvoices(['reseller_id' => $resellerId]);
        $this->updateClientNotes(['reseller_id' => $resellerId]);
        $this->updateSite(['reseller_id' => $resellerId]);
        $this->updateSubscriptions(['reseller_id' => $resellerId]);
        $this->updateTransactionFeeInvoices(['reseller_id' => $resellerId]);
    }

    public function updateBilling(array $attributes): void
    {
        DB::connection('sys-backend')->table('billing_balance_transactions')
            ->where('client_id', $this->getSite()->client_id)
            ->update($attributes);

        DB::connection('sys-backend')->table('billing_invoice_line_items')
            ->where('client_id', $this->getSite()->client_id)
            ->update($attributes);

        DB::connection('sys-backend')->table('billing_invoices')
            ->where('client_id', $this->getSite()->client_id)
            ->update($attributes);

        DB::connection('sys-backend')->table('billing_payouts')
            ->where('client_id', $this->getSite()->client_id)
            ->update($attributes);

        DB::connection('sys-backend')->table('billing_refunds')
            ->where('client_id', $this->getSite()->client_id)
            ->update($attributes);
    }

    public function updateContacts(array $attributes): void
    {
        DB::connection('sys-backend')->table('contacts')
            ->where('client_id', $this->getSite()->client_id)
            ->update($attributes);

        $this->flushSiteCache();
    }

    public function updateInvoices(array $attributes): void
    {
        DB::connection('sys-backend')->table('contacts')
            ->where('client_id', $this->getSite()->client_id)
            ->update($attributes);

        $this->flushSiteCache();
    }

    public function updateClientNotes(array $attributes): void
    {
        DB::connection('sys-backend')->table('notes')
            ->where('parent_type', 'clients')
            ->where('parent_id', $this->getSite()->client_id)
            ->update($attributes);

        $this->flushSiteCache();
    }

    public function updateSubscriptions(array $attributes): void
    {
        DB::connection('sys-backend')->table('subscriptions')
            ->where('client_id', $this->getSite()->client_id)
            ->update($attributes);

        $this->flushSiteCache();
    }

    public function updateTransactionFeeInvoices(array $attributes): void
    {
        DB::connection('sys-backend')->table('transaction_fee_invoices')
            ->where('site_id', $this->getSite()->id)
            ->update($attributes);

        $this->flushSiteCache();
    }

    public function updateQuickStartTask(string $slug, array $attributes): void
    {
        $currentStatus = DB::connection('sys-backend')->table('quickstart_tasks')
            ->where('site_id', $this->getSite()->id)
            ->where('task', $slug)
            ->first(array_keys($attributes));

        $currentStatus = array_map(function ($val) {
            return (bool) $val;
        }, (array) $currentStatus);

        $changed = array_diff_assoc($attributes, array_merge([
            'is_active' => false,
            'is_completed' => false,
            'is_skipped' => false,
        ], $currentStatus));

        if (empty($changed)) {
            return;
        }

        $siteUserId = DB::connection('sys-backend')
            ->table('site_users')
            ->where('site_id', $this->getSite()->id)
            ->where('site_user_id', user()->id)
            ->first();

        // Log changes.
        foreach ($changed as $key => $value) {
            DB::connection('sys-backend')->table('quickstart_task_statuses')
                ->insert([
                    'task' => $slug,
                    'site_id' => $this->getSite()->id,
                    'action_key' => $key,
                    'action_value' => $value,
                    'action_by' => $siteUserId->id ?? null,
                    'action_at' => now()->toDateTimeString(),
                ]);
        }

        // Log new composed status
        DB::connection('sys-backend')->table('quickstart_tasks')
            ->upsert(array_merge($changed, [
                'site_id' => $this->getSite()->id,
                'task' => $slug,
                'created_at' => now()->toDatetimeFormat(),
                'updated_at' => now()->toDatetimeFormat(),
            ]), [
                'site_id',
                'task',
            ], array_merge(array_keys($changed), ['updated_at']));
    }

    /**
     * Adds a note on the client.
     *
     * @param string $body
     */
    public function addNote($body)
    {
        $userId = isDev() ? 1 : 31;

        DB::connection('sys-backend')->table('notes')->insert([
            'reseller_id' => $this->getSite()->reseller_id,
            'parent_type' => 'clients',
            'parent_id' => $this->getSite()->client_id,
            'body' => $body,
            'created_at' => now()->toDatetimeFormat(),
            'updated_at' => now()->toDatetimeFormat(),
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);
    }

    /**
     * Logs a Swift Message.
     */
    public function logSwiftMessage(Swift_Message $message, string $smtpTransactionLog, int $sent): void
    {
        DB::connection('sys-backend')->table('support.swiftmailer')->insert([
            'shop' => sys_get('ds_account_name'),
            'to' => json_encode($message->getTo()),
            'cc' => json_encode($message->getCc()),
            'bcc' => json_encode($message->getBcc()),
            'subject' => $message->getSubject(),
            'smtp_transaction' => $smtpTransactionLog,
            'sent' => $sent,
            'sent_at' => now(),
        ]);
    }

    public function updateSite(array $attributes): void
    {
        DB::connection('sys-backend')
            ->table('sites')
            ->where('id', $this->getSite()->id)
            ->update($attributes);
    }

    public function updateSiteUsers(User $user): void
    {
        if (empty($user->email)) {
            return;
        }

        DB::connection('sys-backend')
            ->table('site_users')->upsert([
                'site_id' => site()->id,
                'site_user_id' => $user->getKey(),
                'email' => $user->email,
                'name' => $user->full_name,
                'phone_primary' => $user->primaryphonenumber,
                'phone_alternate' => $user->alternatephonenumber,
                'is_admin' => $user->isadminuser,
                'is_account_admin' => $user->is_account_admin,
                'last_login_at' => $user->last_login_at,
                'created_at' => $user->createddatetime,
                'updated_at' => $user->modifieddatetime,
                'deleted_at' => $user->deleted_at,
            ], [
                'site_id', 'site_user_id',
            ], [
                'email',
                'name',
                'phone_primary',
                'phone_alternate',
                'is_admin',
                'is_account_admin',
                'last_login_at',
                'created_at',
                'updated_at',
                'deleted_at',
            ]);
    }
}
