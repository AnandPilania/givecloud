<?php

namespace Ds\Http\Controllers;

use Ds\Domain\Flatfile\Services\Supporters as FlatfileSupportersService;
use Ds\Domain\Shared\DataTable;
use Ds\Enums\RecurringPaymentProfileStatus;
use Ds\Enums\Supporters\SupporterStatus;
use Ds\Http\Controllers\Frontend\API\Services\LocaleController;
use Ds\Http\Requests\AccountSaveFormRequest;
use Ds\Jobs\CalculateLifetimeMemberGiving;
use Ds\Models\FundraisingPage;
use Ds\Models\GroupAccount;
use Ds\Models\GroupAccountTimespan;
use Ds\Models\Member;
use Ds\Models\MemberOptinLog;
use Ds\Models\Membership;
use Ds\Models\Product;
use Ds\Services\DonorPerfectService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use LiveControl\EloquentDataTable\ExpressionWithName;

class MemberController extends Controller
{
    public function destroy()
    {
        $member = Member::findOrFail(request('id'));
        $member->is_active = 0;
        $member->save();

        $this->flash->success('Supporter successfully deleted.');

        return redirect()->route('backend.member.index');
    }

    public function restore($member_id)
    {
        $member = Member::findOrFail($member_id);
        $member->is_active = 1;
        $member->save();

        $this->flash->success('Supporter successfully unarchived.');

        return redirect()->route('backend.member.edit', $member_id);
    }

    public function index()
    {
        user()->canOrRedirect('member');

        if (Member::doesntExist()) {
            return view('members.empty');
        }

        return view('members.index', [
            'recurringPaymentProfileStatuses' => RecurringPaymentProfileStatus::all(),
            'donationForms' => Product::query()->donationForms()->get(),
            'flatfileToken' => app(FlatfileSupportersService::class)->token(),
        ]);
    }

    public function listing(): Response
    {
        user()->canOrRedirect('member');

        $activeRpps = DB::table('recurring_payment_profiles')->selectRaw('count(*)')->whereRaw('member.id = recurring_payment_profiles.member_id')->whereRaw("status = 'Active'");
        $suspendedRpps = DB::table('recurring_payment_profiles')->selectRaw('count(*)')->whereRaw('member.id = recurring_payment_profiles.member_id')->whereRaw("status = 'Suspended'");

        $dataTable = new DataTable($this->_baseQueryWithFilters()->with(['accountType', 'latestAvatar']), [
            new ExpressionWithName('member.id', 'checkbox_id'),
            new ExpressionWithName('member.display_name', 'display_name'),
            new ExpressionWithName('member.email', 'email'),
            new ExpressionWithName('member.referral_source', 'referral_source'),
            new ExpressionWithName('(CASE WHEN (' . $activeRpps->toSql() . ') >= 1 THEN 1 WHEN (' . $suspendedRpps->toSql() . ') >= 1 THEN 0 ELSE -1 END)', 'rpp'),
            new ExpressionWithName('member.nps', 'nps'),
            new ExpressionWithName('member.password', 'password'),
            new ExpressionWithName('(member.lifetime_purchase_count + member.lifetime_donation_count)', 'contributions_count'),
            new ExpressionWithName('(member.lifetime_purchase_amount + member.lifetime_donation_amount)', 'contributions_amount'),
            new ExpressionWithName('member.created_at', 'created_at'),

            // Others, for display purposes
            new ExpressionWithName('member.account_type_id', 'account_type_id'),
            new ExpressionWithName('member.bill_organization_name', 'bill_organization_name'),
            new ExpressionWithName('member.first_name', 'first_name'),
            new ExpressionWithName('member.last_name', 'last_name'),
            new ExpressionWithName('member.id', 'id'),
        ]);

        $dataTable->setFormatRowFunction(function ($member) {
            return [
                view('members._listing.checkbox', compact('member'))->render(),
                view('members._listing.supporter', compact('member'))->render(),
                view('members._listing.email', compact('member'))->render(),
                e($member->referral_source),
                view('members._listing.rpp', compact('member'))->render(),
                view('members._listing.nps', compact('member'))->render(),
                view('members._listing.hasLogin', compact('member'))->render(),
                e($member->contributions_count),
                e((string) money($member->contributions_amount)),
                view('members._listing.created', compact('member'))->render(),
                dangerouslyUseHTML('<a href="' . route('backend.member.edit', $member->id) . '">View</a>'),
            ];
        });

        return response($dataTable->make());
    }

    public function batch()
    {
        user()->canOrRedirect('member');

        if (! request()->filled('ids')) {
            $this->flash->error('Failed to batch process. There were no items to process.');

            return redirect()->back();
        }

        $ids = explode(',', request('ids'));

        // mark all as ARCHIVE
        if (request('action') === 'archived') {
            Member::whereIn('id', $ids)->update(['is_active' => 0]);

        // mark all as SPAM
        } elseif (request('action') === 'spam') {
            Member::whereIn('id', $ids)->update(['is_active' => 0, 'is_spam' => 1]);
        }

        $this->flash->success('Successfully processed ' . count($ids) . ' items.');

        return redirect()->back();
    }

    public function export_emails()
    {
        // deny permission
        user()->canOrRedirect('member');

        pageSetup('Export Supporters: Emails', false);

        return response()->streamDownload(function () {
            $outstream = fopen('php://output', 'w');

            $this->_baseQueryWithFilters()
                ->select('member.*')
                ->cursor()
                ->each(function ($member) use ($outstream) {
                    fputcsv(
                        $outstream,
                        [
                            $member->first_name,
                            $member->last_name,
                            $member->email,
                            $member->getShareableLink('/'),
                        ],
                        ',',
                        '"'
                    );
                });

            fclose($outstream);
        }, 'supporter_emails.csv', [
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Content-Description' => 'File Transfer',
            'Content-type' => 'text/csv',
            'Expires' => '0',
            'Pragma' => 'public',
        ]);
    }

    public function export()
    {
        set_time_limit(60 * 10); // 10 minutes

        // deny permission
        user()->canOrRedirect('member');

        pageSetup('Export Supporters: All Data', false);

        return response()->streamDownload(function () {
            $outstream = fopen('php://output', 'w');

            $headers = [
                'Supporter Type', 'Display Name', 'First Name', 'Last Name', 'Organization Name', 'Email', 'Email Opt-in',
                'Shipping Title', 'First Name', 'Last Name', 'Organization Name', 'Email', 'Address', 'Address 2', 'City', 'State/Prov', 'ZIP/Postal', 'Country', 'Phone',
                'Billing Title', 'First Name', 'Last Name', 'Organization Name', 'Email', 'Address', 'Address 2', 'City', 'State/Prov', 'ZIP/Postal', 'Country', 'Phone',
                'Payments', 'Payments Total',
                'DonorPerfect ID', 'Created on', 'Updated on', 'Referral Source',
            ];

            // inner joining on group_account to eliminate
            // memberships to which no one has ever been enrolled
            $memberships = Membership::query()
                ->select([
                    'membership.id',
                    'membership.name',
                    DB::raw('count(group_account.id) as enrollment'),
                ])->join('group_account', 'group_account.group_id', 'membership.id')
                ->groupBy('membership.id')
                ->orderBy('enrollment', 'desc')
                ->toBase()
                ->get();

            foreach ($memberships as $membership) {
                $headers[] = "'$membership->name' Is Active";
                $headers[] = "'$membership->name' Start Date";
                $headers[] = "'$membership->name' End Date";
            }

            fputcsv($outstream, $headers, ',', '"');

            $members = $this->_baseQueryWithFilters()
                ->select('member.*')
                ->with('accountType');

            $members->chunkById((int) sys_get('members_exports_chunk_size'), function ($members) use ($outstream, $memberships) {
                $groupAccounts = GroupAccount::query()
                    ->whereIn('account_id', $members->pluck('id'))
                    ->where(function ($query) {
                        $query->whereNull('start_date');
                        $query->orWhereDate('start_date', '<=', toUtc('today'));
                    })->where(function ($query) {
                        $query->whereNull('end_date');
                        $query->orWhereDate('end_date', '>=', toUtc('today'));
                    })->orderBy('account_id')
                    ->orderBy('start_date', 'desc')
                    ->with('group')
                    ->get()
                    ->groupBy('account_id')
                    ->map(function ($groupAccounts) {
                        return $groupAccounts->groupBy('group_id');
                    });

                foreach ($members as $member) {
                    $data = [
                        $member->accountType->name ?? '',
                        $member->display_name,
                        $member->first_name,
                        $member->last_name,
                        $member->bill_organization_name,
                        $member->email,
                        $member->email_opt_in ? 'Yes' : 'No',
                        $member->ship_title,
                        $member->ship_first_name,
                        $member->ship_last_name,
                        $member->ship_organization_name,
                        $member->ship_email,
                        $member->ship_address_01,
                        $member->ship_address_02,
                        $member->ship_city,
                        $member->ship_state,
                        $member->ship_zip,
                        $member->ship_country,
                        $member->ship_phone,
                        $member->bill_title,
                        $member->bill_first_name,
                        $member->bill_last_name,
                        $member->bill_organization_name,
                        $member->bill_email,
                        $member->bill_address_01,
                        $member->bill_address_02,
                        $member->bill_city,
                        $member->bill_state,
                        $member->bill_zip,
                        $member->bill_country,
                        $member->bill_phone,
                        $member->lifetime_purchase_count + $member->lifetime_donation_count,
                        money($member->lifetime_purchase_amount + $member->lifetime_donation_amount)->format(),
                        $member->donor_id,
                        $member->created_at,
                        $member->updated_at,
                        $member->referral_source,
                    ];

                    $accountGroups = $groupAccounts[$member->id] ?? null;

                    foreach ($memberships as $membership) {
                        $groupAccount = $accountGroups[$membership->id][0] ?? null;

                        if ($groupAccount) {
                            $data[] = $groupAccount->group->name;
                            $data[] = toLocalFormat($groupAccount->start_date, 'csv');
                            $data[] = toLocalFormat($groupAccount->end_date, 'csv');
                        } else {
                            $data[] = null;
                            $data[] = null;
                            $data[] = null;
                        }
                    }

                    fputcsv($outstream, $data, ',', '"');
                }
            }, 'member.id', 'id');

            fclose($outstream);
        }, 'supporters.csv', [
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Content-Description' => 'File Transfer',
            'Content-type' => 'text/csv',
            'Expires' => '0',
            'Pragma' => 'public',
        ]);
    }

    public function save(AccountSaveFormRequest $request, DonorPerfectService $donorPerfectService)
    {
        $is_new = false;

        // if no id passed in, we need to create a new record
        if (! request()->filled('id')) {
            $memberModel = \Ds\Models\Member::newWithPermission();
            $memberModel->sign_up_method = 'manual';
        // if id is available, find a member
        } else {
            $memberModel = \Ds\Models\Member::findWithPermission(request('id'));
        }

        // update member
        $memberModel->title = request('title');
        $memberModel->first_name = request('first_name');
        $memberModel->last_name = request('last_name');
        $memberModel->email = request('email');
        $memberModel->password = (request()->filled('password')) ? bcrypt(request('password')) : $memberModel->password;
        $memberModel->donor_id = (request()->filled('donor_id')) ? request('donor_id') : null;
        $memberModel->sync_status = request('sync_status');
        $memberModel->infusionsoft_contact_id = (request()->filled('infusionsoft_contact_id')) ? request('infusionsoft_contact_id') : null;
        $memberModel->account_type_id = request('account_type_id') ?: $memberModel->account_type_id;
        $memberModel->bill_organization_name = request('bill_organization_name');
        $memberModel->bill_title = request('bill_title');
        $memberModel->bill_first_name = request('bill_first_name');
        $memberModel->bill_last_name = request('bill_last_name');
        $memberModel->bill_email = request('bill_email');
        $memberModel->bill_phone = request('bill_phone');
        $memberModel->bill_address_01 = request('bill_address_01');
        $memberModel->bill_address_02 = request('bill_address_02');
        $memberModel->bill_city = request('bill_city');
        $memberModel->bill_state = request('bill_state');
        $memberModel->bill_zip = request('bill_zip');
        $memberModel->bill_country = request('bill_country');
        $memberModel->ship_title = request('ship_title');
        $memberModel->ship_first_name = request('ship_first_name');
        $memberModel->ship_last_name = request('ship_last_name');
        $memberModel->ship_organization_name = request('ship_organization_name');
        $memberModel->ship_email = request('ship_email');
        $memberModel->ship_phone = request('ship_phone');
        $memberModel->ship_address_01 = request('ship_address_01');
        $memberModel->ship_address_02 = request('ship_address_02');
        $memberModel->ship_city = request('ship_city');
        $memberModel->ship_state = request('ship_state');
        $memberModel->ship_zip = request('ship_zip');
        $memberModel->ship_country = request('ship_country');
        $memberModel->referred_by = request('referred_by');

        if (sys_get('referral_sources_isactive')) {
            $memberModel->referral_source = (request()->filled('referral_source')) ? request('referral_source') : null;
        }

        $shouldActivatedPendingFundraisingPages = false;
        if (sys_get('fundraising_pages_requires_verify')) {
            $memberModel->verified_status = request('verified_status');
            $shouldActivatedPendingFundraisingPages = $memberModel->isDirty('verified_status') && $memberModel->isVerified;
        }

        if (sys_get('nps_enabled')) {
            $memberModel->nps = (request()->filled('nps') && request('nps') >= 0) ? request('nps') : null;
        }

        // hack - there are a bunch of 1970 dates in database - this cleans them up as we go
        if ($memberModel->membership_expires_on && $memberModel->membership_expires_on->year == 1970) {
            $memberModel->membership_expires_on = null;
        }

        $memberModel->save();
        $memberModel->refresh();

        // Update optin once $memberModel has an id.
        $memberModel->email_opt_in = request('email_opt_in');

        // update user defined fields
        $memberModel->syncUserDefinedFields($request->user_defined_fields);

        // push admin created accounts to DP
        if (sys_get('admin_created_accounts_pushed_to_dpo')) {
            $memberModel->verifyDpo();
        }

        // update donors in DP using the gc data
        if (request('update_dpo')) {
            $donor = app('Ds\Services\DonorPerfectService')->updateDonorFromAccount($memberModel);

            if ($memberModel->membershipTimespan) {
                $donorPerfectService->updateDonorMembership($memberModel->donor_id, $memberModel->membershipTimespan->pivot);
            }
        }

        // update donors in Infusionsoft using the gc data
        if (request('update_infusionsoft')) {
            $donor = app('Ds\Services\InfusionsoftService')->pushAccount($memberModel);
        }

        // Send Activated Pages notification
        if ($shouldActivatedPendingFundraisingPages) {
            $memberModel->fundraisingPages()->pending()->each(function (FundraisingPage $fundraisingPage) {
                $fundraisingPage->activate();
            });
        }

        // success
        $this->flash->success(e($memberModel->display_name) . ' updated successfully.');

        return redirect()->route('backend.member.edit', $memberModel->id);
    }

    public function merge($merge_id)
    {
        // check permission
        user()->canOrRedirect('member.merge');

        // get master member id
        $master_id = request('master_member_id');

        // see if master member and merge member are the same
        // master member not found or not specified
        if ($master_id == $merge_id) {
            $this->flash->error('Error merging. The master supporter chosen and merge supporter are the same. Please choose a different master supporter.');

            return redirect()->back();
        }

        // find master member
        $master_member = \Ds\Models\Member::find($master_id);

        // master member not found or not specified
        if (! $master_member) {
            $this->flash->error('Error merging. The master supporter could not be found.');

            return redirect()->back();
        }

        // find merge member
        $merge_member = \Ds\Models\Member::find($merge_id);

        // merge member not found
        if (! $merge_member) {
            $this->flash->error('Error merging. The merge supporter could not be found.');

            return redirect()->back();
        }

        DB::transaction(function () use ($master_id, $master_member, $merge_id, $merge_member) {
            // move all autologin tokens to master member
            \Ds\Models\AutologinToken::where('user_type', 'account')->where('user_id', $merge_id)->update(['user_id' => $master_id]);

            // move all fundraising pages to master member
            \Ds\Models\FundraisingPage::where('member_organizer_id', $merge_id)->update(['member_organizer_id' => $master_id]);

            // move all fundraising page reports to master member
            \Ds\Models\FundraisingPageReport::where('member_id', $merge_id)->update(['member_id' => $master_id]);

            // move all group accounts to master member
            \Ds\Models\GroupAccount::where('account_id', $merge_id)->update(['account_id' => $master_id]);

            // move all group account timespans to master member
            \Ds\Models\GroupAccountTimespan::where('account_id', $merge_id)->update(['account_id' => $master_id]);

            // move all member logins to master member
            \Ds\Models\MemberLogin::where('member_id', $merge_id)->update(['member_id' => $master_id]);

            // move all orders to master member
            \Ds\Models\Order::withSpam()->withTrashed()->where('member_id', $merge_id)->update(['member_id' => $master_id]);

            // move all order item fundraisers to master member
            \Ds\Models\OrderItem::where('fundraising_member_id', $merge_id)->update(['fundraising_member_id' => $master_id]);

            // move all payment methods to master member
            \Ds\Models\Payment::where('source_account_id', $merge_id)->update(['source_account_id' => $master_id]);

            // move all payment methods to master member
            \Ds\Models\PaymentMethod::where('member_id', $merge_id)->update([
                'member_id' => $master_id,
                'use_as_default' => 0,
            ]);

            // move all pledges to master member
            \Ds\Models\Pledge::where('account_id', $merge_id)->update(['account_id' => $master_id]);

            // move all recurring payment profiles to master member
            \Ds\Models\RecurringPaymentProfile::where('member_id', $merge_id)->update(['member_id' => $master_id]);

            // move all resumable conversations to master member
            \Ds\Domain\Messenger\Models\ResumableConversation::where('account_id', $merge_id)->update(['account_id' => $master_id]);

            // move all sponsorships to master member
            \Ds\Domain\Sponsorship\Models\Sponsor::where('member_id', $merge_id)->update(['member_id' => $master_id]);

            // move all tax receipts to master member
            \Ds\Models\TaxReceipt::where('account_id', $merge_id)->update(['account_id' => $master_id]);

            // soft delete the merged account
            $merge_member->is_active = 0;
            $merge_member->save();

            $master_member->refresh();

            // regenerate group account timespans on master member
            $master_member->groups->each(fn ($group) => GroupAccountTimespan::aggregate($group->id, $master_member->id));
        });

        CalculateLifetimeMemberGiving::dispatch($merge_member);
        CalculateLifetimeMemberGiving::dispatch($master_member);

        // success
        $this->flash->success(e($merge_member->display_name) . ' was merged into ' . e($master_member->display_name) . ' successfully.');

        // redirect to the master member account detail screen
        return redirect()->route('backend.member.edit', $master_member);
    }

    public function view($id = null)
    {
        $__menu = 'accounts';

        $account_types = \Ds\Models\AccountType::all();

        $account_id = $id ?? request('i');

        if ($account_id) {
            $member = \Ds\Models\Member::findWithPermission($account_id);
            $isNew = 0;
            $title = $member->display_name;
        } else {
            $member = \Ds\Models\Member::newWithPermission();
            $isNew = 1;
            $title = 'Add Supporter';
        }

        $groups = $member->groups->groupBy('id')
            ->map(function ($groups) {
                return $groups->sortByDesc(function ($group) {
                    return [$group->pivot->is_active, $group->pivot->start_date];
                })->first();
            });

        $member->load([
            'accountType',
            'orders',
            'paymentMethods',
            'recurringPaymentProfiles',
            'referrals',
            'sponsors',
            'transactions.paymentMethod',
            'transactions.recurringPaymentProfile',
            'groupAccountTimespans',
        ]);

        $orderCount = $member->orders->count();

        $paymentCount = $member->payments()->succeededOrPending()->count();

        $fundraisingPagesCount = $member->fundraisingPages()->count();

        $sponsorshipCount = $member->sponsorships()->count();

        $countries = cart_countries();
        asort($countries);

        $billingSubdivisions = app(LocaleController::class)->getSubdivisions($member->bill_country)->getData(true);
        $shippingSubdivisions = app(LocaleController::class)->getSubdivisions($member->ship_country)->getData(true);

        $salesforceReference = $member->references()->supporter()->salesforce()->latest()->first();

        pageSetup($title, 'jpanel');

        return $this->getView(
            'members/view',
            compact(
                '__menu',
                'account_types',
                'member',
                'isNew',
                'title',
                'groups',
                'orderCount',
                'paymentCount',
                'fundraisingPagesCount',
                'sponsorshipCount',
                'salesforceReference',
                'countries',
                'billingSubdivisions',
                'shippingSubdivisions',
            )
        );
    }

    /**
     * Allows an admin user to login to the public
     * facing website as a mamber.
     *
     * @param string $id
     */
    public function loginAs($id)
    {
        // find with permission
        $member = Member::findWithPermission($id, 'view', route('backend.member.edit', $id));

        // check login permission
        $member->userCanOrRedirect('login', route('backend.member.edit', $id));

        // for users who have 3rd-party cookie support either blocked or disabled that
        // prevents the cross-domain session sharing beacon from working. so if they are using
        // jpanel from a domain other than the primary domain as a workaround to mitigate
        // the session issues we are preemptively redirecting them to the primary domain
        if (site()->domain !== site()->primary_domain && ! sys_get('custom_domain_migration_mode')) {
            return redirect()->to(sprintf(
                'https://%s%s',
                site()->primary_domain,
                route('backend.members.login', $member->id, false)
            ));
        }

        if ($member->force_password_reset) {
            $member->force_password_reset = false;
            $member->save();
        }

        // login as the member
        member_login_with_id($member->id);

        cart()->populateMember($member->id);

        // redirect to public site
        $redirectTo = data_get(
            $member,
            'membership.default_url',
            secure_site_url('account/home')
        );

        return redirect()->to($redirectTo);
    }

    /**
     * Autocomplete a selectize input.
     * GET method
     * Expects 'query' input (search terms)
     */
    public function autocomplete()
    {
        $keywords = strtolower(trim(request()->nonArrayInput('query')));

        if ($keywords === '') {
            return response()->json([]);
        }

        // get members
        $matches = Member::select('id', 'display_name', 'first_name', 'last_name', 'email', 'bill_address_01', 'bill_address_02', 'bill_city', 'bill_state', 'bill_zip', 'bill_country', 'bill_phone', 'donor_id', 'account_type_id')
            ->where('is_active', 1)
            ->where(function ($qry) use ($keywords) {
                $qry->whereRaw("lower(concat(display_name, ' ', ifnull(email,''), ' ', ifnull(bill_address_01,''), ' ', ifnull(bill_address_02,''), ' ', ifnull(bill_city,''), ' ', ifnull(bill_state,''), ' ', ifnull(bill_zip,''), ' ', ifnull(bill_phone,''))) LIKE ?", ['%' . $keywords . '%'])
                    ->orWhere('donor_id', '=', $keywords);
            })->orderBy('display_name')
            ->orderBy('email')
            ->with('accountType', 'groups')
            ->take(200)
            ->cursor();

        // build json to return
        $json = [];
        foreach ($matches as $member) {
            $json[] = [
                'id' => $member->id,
                'display_name' => $member->display_name,
                'email' => $member->email,
                'display_bill_address' => str_replace(chr(10), ', ', $member->display_bill_address) . ((dpo_is_enabled() && $member->donor_id) ? ' (DPO Donor ID: ' . $member->donor_id . ')' : ''),
                'display_bill_phone' => $member->display_bill_phone,
                'icon' => $member->fa_icon,
                'membership_expires_on' => (feature('membership') && $member->membership_expires_on) ? toLocalFormat($member->membership_expires_on, 'date') : null,
                'membership_name' => (feature('membership')) ? $member->membership->name ?? null : null,
                'membership_is_expired' => (feature('membership')) ? $member->is_membership_expired : null,
            ];
        }

        return response()->json($json);
    }

    /**
     * Build a base query based on request filter params.
     * Allows us to reuse this for datatables, csv, etc...
     */
    private function _baseQueryWithFilters()
    {
        // base query
        $members = Member::query()
            ->groupBy('member.id');

        // active (1) / inactive (0) (Archived) / all (2) / spam (3)
        $statusFilter = (int) request('fA', SupporterStatus::ACTIVE);
        $members->when(in_array($statusFilter, [SupporterStatus::ACTIVE, SupporterStatus::ARCHIVED], true), function ($query) use ($statusFilter) {
            $query->where('member.is_active', $statusFilter === SupporterStatus::ACTIVE);
        });

        if ($statusFilter === SupporterStatus::SPAM) {
            $members->onlySpam();
        }

        // keyword search (name, email)
        if (request('fB')) {
            $members->where(function ($query) {
                $keyword = '%' . request('fB') . '%';
                $query->where('member.display_name', 'like', $keyword);
                $query->orWhere('member.email', 'like', $keyword);
                $query->orWhere('member.ship_email', 'like', $keyword);
                $query->orWhere('member.bill_email', 'like', $keyword);
                $query->orWhere('member.ship_phone', 'like', $keyword);
                $query->orWhere('member.bill_phone', 'like', $keyword);
            });
        }

        // dates
        if (request('fd1') != '' && request('fd2') != '') {
            $members->where(DB::raw('CAST(member.created_at AS DATE)'), '>=', request('fd1'));
            $members->where(DB::raw('CAST(member.created_at AS DATE)'), '<=', request('fd2'));
        } elseif (request('fd1') != '') {
            $members->where(DB::raw('CAST(member.created_at AS DATE)'), '>=', request('fd1'));
        } elseif (request('fd2') != '') {
            $members->where(DB::raw('CAST(member.created_at AS DATE)'), '<=', request('fd2'));
        }

        // First Payment dates
        $members->when(
            $firstPaymentAfter = toUtc(request('firstPaymentAfter')),
            function (Builder $query) use ($firstPaymentAfter) {
                $query->where('first_payment_at', '>=', $firstPaymentAfter);
            }
        );

        $members->when(
            $firstPaymentBefore = toUtc(request('firstPaymentBefore')),
            function (Builder $query) use ($firstPaymentBefore) {
                $query->where('first_payment_at', '<=', $firstPaymentBefore);
            }
        );

        // Last Payment dates
        $members->when(
            $lastPaymentAfter = toUtc(request('lastPaymentAfter')),
            function (Builder $query) use ($lastPaymentAfter) {
                $query->where('last_payment_at', '>=', $lastPaymentAfter);
            }
        );

        $members->when(
            $lastPaymentBefore = toUtc(request('lastPaymentBefore')),
            function (Builder $query) use ($lastPaymentBefore) {
                $query->where('last_payment_at', '<=', $lastPaymentBefore);
            }
        );

        // memberships
        if (request('membership_id') != '') {
            $members->leftJoin('group_account as fmGA', 'fmGA.account_id', 'member.id');
            $members->leftJoin('membership as fmG', 'fmG.id', 'fmGA.group_id');
            $members->whereIn('fmGA.group_id', explode(',', request('membership_id')));
            $members->where(function ($query) {
                $query->whereNull('fmGA.start_date');
                $query->orWhere('fmGA.start_date', '<=', fromLocal('today'));
            });
            $members->where(function ($query) {
                $query->whereNull('fmGA.end_date');
                $query->orWhere('fmGA.end_date', '>=', fromLocal('today'));
            });
        }

        //Donation forms
        if (request('donationForms')) {
            $members->whereHas('orders', function (Builder $query) {
                $query->whereHas('items', function ($query) {
                    $query->whereHas('variant', function ($query) {
                        $hashIds = collect(explode(',', request('donationForms')))->map(fn ($hash) => Product::decodeHashid($hash));
                        $query->whereIn('productinventory.productid', $hashIds);
                    });
                });
            });
        }

        // referral sources
        if (request('fr')) {
            $members->whereIn('member.referral_source', explode(',', request('fr')));
        }

        // account_types
        if (request('ft')) {
            $members->whereIn('member.account_type_id', explode(',', request('ft')));
        }

        if (in_array(request('rpp'), RecurringPaymentProfileStatus::all(), true)) {
            $members->whereHas('recurringPaymentProfiles', function (Builder $query) {
                return $query->{request('rpp')}();
            });
        }

        // nps
        if (request('fn')) {
            if (strpos(request('fn'), ':') !== false) {
                $range = collect(explode(':', request('fn')))->map(function ($v) {
                    return (float) preg_replace('/[^0-9,.]/', '', $v);
                });
                if ($range->count() == 2) {
                    $members->whereBetween('member.nps', $range->all());
                }
            } elseif (strpos(request('fn'), '<') !== false) {
                $members->where('member.nps', '<=', (float) preg_replace('/[^0-9,.]/', '', request('fn')));
            } elseif (strpos(request('fn'), '>') !== false) {
                $members->where('member.nps', '>=', (float) preg_replace('/[^0-9,.]/', '', request('fn')));
            }
        }

        request()->whenFilled('is_slipping', function ($isSlipping) use ($members) {
            $members->when(
                $isSlipping,
                function (Builder $query) {
                    $query->whereHas('recurringPaymentProfiles', function (Builder $query) {
                        $query->suspended();
                    })->orWhere(function (Builder $query) {
                        $query->whereDoesntHave('paymentMethods', function (Builder $query) {
                            $query->active()->notExpiringByEndNextMonth();
                        })->whereHas('paymentMethods', function (Builder $query) {
                            $query->active()->expiringByEndOfNextMonth();
                        });
                    });
                },
                function (Builder $query) {
                    $query->whereDoesntHave('recurringPaymentProfiles', function (Builder $query) {
                        $query->suspended();
                    })->where(function (Builder $query) {
                        $query->whereHas('paymentMethods', function (Builder $query) {
                            $query->active()->notExpiringByEndNextMonth();
                        })->orWhereDoesntHave('paymentMethods');
                    });
                },
            );
        });

        request()->whenFilled('used_text_to_give', function ($usedTextToGive) use ($members) {
            $members->when($usedTextToGive, function (Builder $query) {
                $query->whereHas('orders', function (Builder $query) {
                    $query->conversation();
                });
            });
        });

        request()->whenFilled('has_login', function ($hasLogin) use ($members) {
            $members->where(function () use ($hasLogin, $members) {
                $members->{$hasLogin ? 'whereNotNull' : 'whereNull'}('member.password')
                    ->orWhereHas('socialIdentities');
            });
        });

        $hasLoggedInFilter = request()->filled('has_logged_in');
        $hasLoggedIn = (bool) request('has_logged_in');

        $members->when($hasLoggedInFilter && $hasLoggedIn, function (Builder $query) {
            $query->whereHas('ownLoginLogs');
        });

        $members->when($hasLoggedInFilter && ! $hasLoggedIn, function (Builder $query) {
            $query->whereDoesntHave('ownLoginLogs');
        });

        $this->addFundraisersFilter($members);

        // email opt-in
        request()->whenFilled('fe', function () use ($members) {
            $lastAction = MemberOptinLog::query()
                ->select('member_id', 'action', DB::raw('max(created_at) as max_created_at'))
                ->groupBy('member_id');

            $members->rightJoinSub($lastAction, 'lastAction', function (JoinClause $join) {
                $join->on('member.id', 'lastAction.member_id')
                    ->where('lastAction.action', request('fe') === '1' ? 'optin' : 'optout');
            });
        });

        $this->paymentMethodsFilters($members);

        $members->when(request('verified_status') === '1', function (Builder $query) {
            $query->verified();
        });

        $members->when(request('verified_status') === '0', function (Builder $query) {
            $query->pending();
        });

        $members->when(request('verified_status') === '-1', function (Builder $query) {
            $query->denied();
        });

        $members->when(request('verified_status') === '2', function (Builder $query) {
            $query->unverified();
        });

        return $members;
    }

    private function paymentMethodsFilters(Builder $members)
    {
        $members->when(request()->filled('payment_method') && request('payment_method') === 'valid', function (Builder $query) {
            $query->whereHas('paymentMethods', function (Builder $query) {
                $query->valid();
            });
        });

        $members->when(request()->filled('payment_method') && request('payment_method') === 'none', function (Builder $query) {
            $query->doesntHave('paymentMethods');
        });

        $members->when(request()->filled('payment_method') && request('payment_method') === 'expiring', function (Builder $query) {
            $query->whereDoesntHave('paymentMethods', function (Builder $query) {
                $query->active()->notExpiringByEndNextMonth();
            })->whereHas('paymentMethods', function (Builder $query) {
                $query->active()->expiringByEndOfNextMonth();
            });
        });

        $members->when(request()->filled('payment_method') && request('payment_method') === 'expired', function (Builder $query) {
            $query->whereDoesntHave('paymentMethods', function (Builder $query) {
                $query->valid();
            })->whereHas('paymentMethods', function (Builder $query) {
                $query->isExpired();
            });
        });
    }

    private function addFundraisersFilter(Builder $query): void
    {
        $scope = null;
        $filters = explode(',', request('fundraisers'));

        if (in_array('active', $filters, true)) {
            $scope = in_array('closed', $filters, true)
                ? 'activeOrClosed'
                : 'active';
        } elseif (in_array('closed', $filters, true)) {
            $scope = 'closed';
        }

        if ($scope) {
            $query->whereHas('fundraisingPages', function (Builder $query) use ($scope) {
                $query->{$scope}();
            });
        }

        if (in_array('never', $filters, true)) {
            $query->doesntHave('fundraisingPages');
        }
    }

    /**
     * Allow a nonprofit staffer to add a payment method to
     * a profile using the vault ID from safesave.
     *
     * Use case - A vault ID is somehow created OUTSIDE of
     * givecloud.
     *
     * @param string $member_id
     */
    public function import_payment_method_from_vault($member_id)
    {
        // find with permission
        $member = Member::findWithPermission($member_id, 'view', route('backend.member.edit', $member_id));

        // try the import
        try {
            $new_method = app('\Ds\Repositories\PaymentMethodRepository')->createFromVault(request('vault_id'), $member);

            if (request()->input('set_as_default') == 1) {
                $new_method->useAsDefaultPaymentMethod();
            }
            $this->flash->success('Payment method (' . $new_method->display_name . ') imported successfully.');
        } catch (\Exception $e) {
            $this->flash->error($e->getMessage());
        }

        // redirect to public site
        return redirect()->route('backend.member.edit', $member->id);
    }
}
