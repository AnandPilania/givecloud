<?php

namespace Ds\Http\Controllers;

use Ds\Common\Infusionsoft\Api as InfusionsoftApi;
use Ds\Domain\Commerce\Currency;
use Ds\Domain\MissionControl\MissionControlService;
use Ds\Domain\QuickStart\Events\QuickStartTaskAffected;
use Ds\Domain\QuickStart\Tasks\DonorPerfectIntegration;
use Ds\Domain\QuickStart\Tasks\TaxReceipts;
use Ds\Domain\QuickStart\Tasks\TaxReceiptTemplates;
use Ds\Domain\Settings\EmailSettingsService;
use Ds\Domain\Settings\Integrations\IntegrationSettingsService;
use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Jobs\BroadcastDomainChange;
use Ds\Models\TaxReceipt;
use Ds\Models\TaxReceiptTemplate;
use Ds\Services\DonorPerfectService;
use Ds\Services\InfusionsoftService;
use Ds\Services\SupporterVerificationStatusService;
use Faker\Generator as Faker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class SettingController extends Controller
{
    /** @var \Ds\Domain\Settings\EmailSettingsService */
    private $emailSettingsService;

    /** @var \Ds\Domain\Settings\Integrations\IntegrationSettingsService */
    private $integrationSettingsService;

    public function __construct(EmailSettingsService $emailSettingsService, IntegrationSettingsService $integrationSettingsService)
    {
        parent::__construct();

        $this->emailSettingsService = $emailSettingsService;
        $this->integrationSettingsService = $integrationSettingsService;
    }

    public function index()
    {
        user()->canOrRedirect('admin.advanced');

        $__menu = 'admin.advanced';

        pageSetup('Settings', 'jpanel');

        return $this->getView('settings/index', compact('__menu'));
    }

    public function home()
    {
        $__menu = 'admin.advanced';

        pageSetup('Settings', 'jpanel');

        $integrations = $this->integrationSettingsService->getAll()->filter(function ($integration) {
            return $integration->installed && $integration->user_can_administer;
        });

        return $this->getView('settings/home', compact('__menu', 'integrations'));
    }

    public function integrations()
    {
        // get integrations sorted by installed, then by name
        $integrations = $this->integrationSettingsService->getAll()
            ->sortBy(fn ($integration) => $integration->isDeprecated())
            ->sortBy('name');

        // categories sorted by name
        $categories = $integrations->sortBy('category')->pluck('category')->unique()->all();

        // number of installed integrations
        $installed_count = $integrations->where('installed', true)->count();

        return view('settings.integrations.index', compact('integrations', 'categories', 'installed_count'));
    }

    public function dcc()
    {
        $__menu = 'admin.dcc';

        pageSetup('Donor Covers Costs', 'jpanel');

        return $this->getView('settings/dcc', compact('__menu'));
    }

    public function dcc_save()
    {
        $__menu = 'admin.dcc';

        request()->merge([
            'dcc_enabled' => request('dcc_enabled', 0),
            'dcc_enabled_on_sponsorships' => request('dcc_enabled_on_sponsorships', 0),
            'dcc_cost_per_order' => request('dcc_cost_per_order', 0),
            'dcc_percentage' => request('dcc_percentage', 0),
        ]);

        if (sys_set()) {
            $this->flash->success('Settings saved successfully!');
        } else {
            $this->flash->error('There was a problem saving your changes.');
        }

        return redirect()->back();
    }

    public function fundraisingPages()
    {
        if (! feature('fundraising_pages')) {
            return abort(404);
        }

        $__menu = 'admin.advanced';

        user()->canOrRedirect('fundraisingpages');

        pageSetup('Fundraising Pages', 'jpanel');

        return $this->getView('settings/fundraising_pages', compact('__menu'));
    }

    public function fundraisingPages_save()
    {
        if (! feature('fundraising_pages')) {
            return abort(404);
        }

        $__menu = 'admin.advanced';

        user()->canOrRedirect('fundraisingpages');

        request()->merge([
            'fundraising_pages_enabled' => request('fundraising_pages_enabled', 0),
            'fundraising_pages_requires_verify' => request('fundraising_pages_requires_verify', 0),
            'fundraising_pages_auto_verifies' => request('fundraising_pages_auto_verifies', 0),
            'fundraising_pages_require_guideline_acceptance' => request('fundraising_pages_require_guideline_acceptance', 0),
            'fundraising_pages_profanity_filter' => request('fundraising_pages_profanity_filter', 0),
        ]);

        if (! sys_set()) {
            $this->flash->error('There was a problem saving your changes.');
        }

        if (request('fundraising_pages_requires_verify') && request('fundraising_pages_verify_former_pages')) {
            app(SupporterVerificationStatusService::class)->updateSupporterWithActivePages();
            sys_set('fundraising_pages_did_verify_former_pages', 1);
        }

        if (is_array(request('notify_fundraising_page_activated'))) {
            \Ds\Models\User::whereIn('id', request('notify_fundraising_page_activated'))->update(['notify_fundraising_page_activated' => 1]);
            \Ds\Models\User::whereNotIn('id', request('notify_fundraising_page_activated'))->update(['notify_fundraising_page_activated' => 0]);
        } else {
            \Ds\Models\User::query()->update(['notify_fundraising_page_activated' => 0]);
        }

        if (is_array(request('notify_fundraising_page_edited'))) {
            \Ds\Models\User::whereIn('id', request('notify_fundraising_page_edited'))->update(['notify_fundraising_page_edited' => 1]);
            \Ds\Models\User::whereNotIn('id', request('notify_fundraising_page_edited'))->update(['notify_fundraising_page_edited' => 0]);
        } else {
            \Ds\Models\User::query()->update(['notify_fundraising_page_edited' => 0]);
        }

        if (is_array(request('notify_fundraising_page_closed'))) {
            \Ds\Models\User::whereIn('id', request('notify_fundraising_page_closed'))->update(['notify_fundraising_page_closed' => 1]);
            \Ds\Models\User::whereNotIn('id', request('notify_fundraising_page_closed'))->update(['notify_fundraising_page_closed' => 0]);
        } else {
            \Ds\Models\User::query()->update(['notify_fundraising_page_closed' => 0]);
        }

        if (is_array(request('notify_fundraising_page_abuse'))) {
            \Ds\Models\User::whereIn('id', request('notify_fundraising_page_abuse'))->update(['notify_fundraising_page_abuse' => 1]);
            \Ds\Models\User::whereNotIn('id', request('notify_fundraising_page_abuse'))->update(['notify_fundraising_page_abuse' => 0]);
        } else {
            \Ds\Models\User::query()->update(['notify_fundraising_page_abuse' => 0]);
        }

        if (is_array(request('fundraising_product_ids'))) {
            \Ds\Models\Product::whereIn('id', request('fundraising_product_ids'))->update(['allow_fundraising_pages' => 1]);
            \Ds\Models\Product::whereNotIn('id', request('fundraising_product_ids'))->update(['allow_fundraising_pages' => 0]);
        } else {
            \Ds\Models\Product::query()->update(['allow_fundraising_pages' => 0]);
        }

        $this->flash->success('Fundraising page settings successfully updated!');

        return redirect()->to('jpanel/settings/fundraising-pages');
    }

    public function dp(DonorPerfectService $dpo)
    {
        user()->canOrRedirect('admin.dpo');

        $account_types = \Ds\Models\AccountType::whereNotNull('dp_code')
            ->get()->pluck('dp_code')->toArray();

        $dp_field_options = collect();
        $verified_donor_types = collect();

        if (dpo_is_connected()) {
            try {
                $dp_field_options = $dpo->getGiftUdfs();
                $verified_donor_types = app('dpo')->table('dpcodes')
                    ->select('code')
                    ->where('inactive', '=', 'N')
                    ->where('field_name', '=', 'DONOR_TYPE')
                    ->whereIn('code', $account_types)
                    ->orderBy('code')
                    ->get();
            } catch (Throwable $e) {
                // do nothing
            }
        }

        if (count($verified_donor_types) > 0) {
            $verified_donor_types = $verified_donor_types->pluck('code')->toArray();
        }

        pageSetup('DonorPerfect', 'jpanel');

        return $this->getView('settings/dp', [
            '__menu' => 'admin.advanced',
            'account_types' => $account_types,
            'dp_field_options' => $dp_field_options,
            'verified_donor_types' => $verified_donor_types,
        ]);
    }

    public function dp_save()
    {
        $__menu = 'admin.advanced';

        user()->canOrRedirect('admin.dpo');

        if (dpo_is_connected()) {
            request()->merge([
                'dp_auto_sync_orders' => request('dp_auto_sync_orders', 0),
                'dp_auto_sync_txns' => request('dp_auto_sync_txns', 0),
                'dp_push_order_refunds' => request('dp_push_order_refunds', 0),
                'dp_push_txn_refunds' => request('dp_push_txn_refunds', 0),
                'admin_created_accounts_pushed_to_dpo' => request('admin_created_accounts_pushed_to_dpo', 0),
                'allow_account_users_to_update_donor' => request('allow_account_users_to_update_donor', 0),
                'dp_push_mcat_enroll_date' => request('dp_push_mcat_enroll_date', 0),
                'dp_tribute_details_to_narrative' => request('dp_tribute_details_to_narrative', 0),
                'dp_tribute_message_to_narrative' => request('dp_tribute_message_to_narrative', 0),
                'dp_logging' => request('dp_logging', 0),
                'dp_product_codes_override' => request('dp_product_codes_override', 0),
                'dp_use_link_scope' => request('dp_use_link_scope', 0),
                'dp_enable_ty_date' => request('dp_enable_ty_date', 0),
                'dp_order_comments_to_narrative' => request('dp_order_comments_to_narrative', 0),
                'dp_sync_noemail' => request('dp_sync_noemail', 0),
                'dp_sync_salutation' => request('dp_sync_salutation', 0),
                'dp_dcc_is_separate_gift' => request('dp_dcc_is_separate_gift', 0),
                'dp_trigger_calculated_fields' => request('dp_trigger_calculated_fields', 0),
            ]);
        }

        if (sys_set()) {
            $this->flash->success('DonorPerfect integration successfully updated!');
        } else {
            $this->flash->error('There was a problem saving your changes.');
        }

        QuickStartTaskAffected::dispatch(DonorPerfectIntegration::initialize());

        return redirect()->to('jpanel/settings/dp');
    }

    public function dp_pull_donor_data()
    {
        $options = request()->input('options');
        $options['notify_email'] = user('email');

        try {
            dispatch(new \Ds\Jobs\PullDonorDataFromDP($options));

            $this->flash->success("We've started updating your Givecloud supporters. This could take a while. We'll send you an email when it's done.");
        } catch (\Exception $e) {
            $this->flash->error('There was a problem starting the update process. Please try again.');
        }

        return redirect()->to('jpanel/settings/dp');
    }

    public function infusionsoft()
    {
        user()->canOrRedirect('admin.infusionsoft');

        try {
            $categories = app('Ds\Services\InfusionsoftService')->getTagsByCategory();
        } catch (Throwable $e) {
            $categories = [];
        }

        pageSetup('Infusionsoft', 'jpanel');

        return $this->getView('settings/infusionsoft', [
            '__menu' => 'admin.advanced',
            'authorization_link' => app(\Ds\Common\Infusionsoft\Api::class)->getAuthorizationUrl(),
            'infusion_tags_by_categories' => $categories,
        ]);
    }

    public function infusionsoft_save()
    {
        if (sys_set()) {
            $this->flash->success('Settings saved successfully!');
        } else {
            $this->flash->error('There was a problem saving your changes.');
        }

        return redirect()->back();
    }

    public function infusionsoft_connect(InfusionsoftApi $api)
    {
        user()->canOrRedirect('admin.infusionsoft');

        if (request('code')) {
            $api->requestToken(request('code'));

            if (sys_get('infusionsoft_token')) {
                $this->flash->success('Infusionsoft integration successfully updated!');

                return redirect()->to('jpanel/settings/infusionsoft');
            }
        }

        $this->flash->error('There was a problem with the Infusionsoft onboarding.');

        return redirect()->to('jpanel/settings/infusionsoft');
    }

    public function infusionsoft_disconnect()
    {
        $__menu = 'admin.advanced';

        user()->canOrRedirect('admin.infusionsoft');

        sys_set(['infusionsoft_token' => null]);

        $this->flash->success('Infusionsoft integration successfully disconnected!');

        return redirect()->to('jpanel/settings/infusionsoft');
    }

    public function infusionsoft_test(InfusionsoftService $infusionsoft)
    {
        $__menu = 'admin.advanced';

        user()->canOrRedirect('admin.infusionsoft');

        try {
            $infusionsoft->getClient()->contactsWhere(['given_name' => 'KfypquKan2vjRwhMtXk2']);
        } catch (Throwable $e) {
            return response()->json(false);
        }

        return response()->json(true);
    }

    public function shipstation()
    {
        $__menu = 'admin.advanced';

        user()->canOrRedirect('admin.shipstation');

        if (! sys_get('shipstation_user')) {
            sys_set([
                'shipstation_user' => sys_get('ds_account_name'),
                'shipstation_pass' => Str::random(18),
            ]);
        }

        pageSetup('ShipStation', 'jpanel');

        return $this->getView('settings/shipstation', compact('__menu'));
    }

    public function shipstation_save()
    {
        $__menu = 'admin.advanced';

        user()->canOrRedirect('admin.shipstation');

        return redirect()->to('jpanel/settings/shipstation');
    }

    /* settings controller */
    public function accounts()
    {
        $__menu = 'admin.advanced';

        user()->canOrRedirect('admin.accounts');

        pageSetup('Supporter Settings', 'jpanel');

        return $this->getView('settings/accounts', compact('__menu'));
    }

    public function accounts_save()
    {
        $__menu = 'admin.advanced';

        user()->canOrRedirect('admin.accounts');

        if (request('donor_title-is_enabled')) {
            $donorTitle = request('donor_title-is_required') ? 'required' : 'optional';
        } else {
            $donorTitle = 'hidden';
        }

        if (request('hide_other_countries')) {
            $forceCountry = request('default_country');
        } else {
            $forceCountry = null;
        }

        request()->merge([
            'referral_sources_isactive' => request('referral_sources_isactive', 0),
            'referral_sources_other' => request('referral_sources_other', 0),
            'referral_sources_options' => request('referral_sources_options', ''),
            'donor_title' => $donorTitle,
            'donor_title_options' => request('donor_title_options', ''),
            'force_country' => $forceCountry,
            'allow_account_types_on_web' => request('allow_account_types_on_web', ''),
            'nps_enabled' => request('nps_enabled', 0),
            'marketing_optout_reason_required' => request('marketing_optout_reason_required', 0),
            'feature_social_login' => request('feature_social_login', 0),
        ]);

        if (sys_set()) {
            $this->flash->success('Account settings successfully updated!');
        } else {
            $this->flash->error('There was a problem saving your changes.');
        }

        return redirect()->route('backend.settings.supporters');
    }

    public function taxcloud()
    {
        $__menu = 'admin.advanced';

        user()->canOrRedirect('admin.taxcloud');

        pageSetup('TaxCloud', 'jpanel');

        return $this->getView('settings/taxcloud', compact('__menu'));
    }

    public function taxcloud_save()
    {
        $__menu = 'admin.advanced';

        user()->canOrRedirect('admin.taxcloud');

        if (sys_set()) {
            $this->flash->success('TaxCloud settings saved successfully!');
        } else {
            $this->flash->error('There was a problem saving your changes.');
        }

        return redirect()->to('jpanel/settings/taxcloud');
    }

    public function payments()
    {
        user()->canOrRedirect('admin.general');

        $currencies = collect(Currency::getCurrencies());

        $pinnedCurrencies = [
            'USD' => $currencies['USD'],
            'CAD' => $currencies['CAD'],
            'GBP' => $currencies['GBP'],
            'EUR' => $currencies['EUR'],
            'AUD' => $currencies['AUD'],
            'NZD' => $currencies['NZD'],
        ];

        $otherCurrencies = $currencies->reject(function ($currency) use ($pinnedCurrencies) {
            return array_key_exists($currency['code'], $pinnedCurrencies);
        });

        return view('settings/payments', [
            '__menu' => 'admin.advanced',
            'pinned_currencies' => $pinnedCurrencies,
            'other_currencies' => $otherCurrencies,
        ]);
    }

    public function payments_save()
    {
        $__menu = 'admin.advanced';

        user()->canOrRedirect('admin.general');

        request()->merge([
            'rpp_cancel_allow_other_reason' => request('rpp_cancel_allow_other_reason', 0),
        ]);

        if (sys_set()) {
            $this->flash->success('Payment data successfully updated!');
        } else {
            $this->flash->error('There was a problem saving your changes.');
        }

        return redirect()->to('jpanel/settings/payments');
    }

    public function taxReceipts()
    {
        user()->canOrRedirect('taxreceipt.edit');

        pageSetup('Tax Receipts', 'jpanel');

        return view('settings.tax_receipts', [
            '__menu' => 'admin.advanced',
            'templates' => TaxReceiptTemplate::query()
                ->where('template_type', 'template')
                ->orderBy('created_at', 'desc')
                ->get(),
        ]);
    }

    public function taxReceipts_save()
    {
        $__menu = 'admin.advanced';

        user()->canOrRedirect('taxreceipt.edit');

        request()->merge([
            'tax_receipt_pdfs' => request('tax_receipt_pdfs', 0),
            'tax_receipt_summary_include_description' => request('tax_receipt_summary_include_description', 0),
            'tax_receipt_summary_include_gl' => request('tax_receipt_summary_include_gl', 0),
        ]);

        if (sys_set()) {
            $this->flash->success('Tax receipt settings successfully updated!');
        } else {
            $this->flash->error('There was a problem saving your changes.');
        }

        QuickStartTaskAffected::dispatch(TaxReceipts::initialize());
        QuickStartTaskAffected::dispatch(TaxReceiptTemplates::initialize());

        return redirect()->to('jpanel/settings/tax_receipts');
    }

    public function taxReceiptTemplate($id)
    {
        user()->canOrRedirect('taxreceipt.edit');

        pageSetup('Tax Receipt Template', 'jpanel');

        return view('settings.tax_receipt_template', [
            '__menu' => 'admin.advanced',
            'template' => TaxReceiptTemplate::query()
                ->where('template_type', 'template')
                ->findOrFail($id),
        ]);
    }

    public function taxReceiptTemplate_save($id)
    {
        user()->canOrRedirect('taxreceipt.edit');

        $template = TaxReceiptTemplate::query()
            ->where('template_type', 'template')
            ->findOrFail($id);

        $template->name = request('name');
        $template->body = request('body');
        $template->save();

        if (request('is_default')) {
            TaxReceiptTemplate::query()
                ->where('template_type', 'template')
                ->update([
                    'is_default' => 0,
                ]);

            $template->is_default = true;
            $template->save();
        }

        $this->flash->success('Tax receipt template successfully updated!');

        return redirect("/jpanel/settings/tax_receipts/templates/{$template->id}");
    }

    public function taxReceiptTemplate_duplicate($id)
    {
        user()->canOrRedirect('taxreceipt.edit');

        $template = TaxReceiptTemplate::query()
            ->where('template_type', 'template')
            ->findOrFail($id)
            ->replicate([
                'is_default',
                'latest_revision_id',
            ]);

        $template->name = "{$template->name} (Copy)";
        $template->save();

        $this->flash->success('Tax receipt template successfully duplicated!');

        return redirect("/jpanel/settings/tax_receipts/templates/{$template->id}");
    }

    public function taxReceiptTemplate_preview($id)
    {
        user()->canOrRedirect('taxreceipt.edit');

        $template = TaxReceiptTemplate::query()
            ->where('template_type', 'template')
            ->findOrFail($id);

        $faker = app(Faker::class);

        $receipt = new TaxReceipt;
        $receipt->receipt_type = 'consolidated';
        $receipt->first_name = $faker->firstName;
        $receipt->last_name = $faker->lastName;
        $receipt->name = $receipt->full_name;
        $receipt->email = $faker->email;
        $receipt->address_01 = $faker->streetAddress;
        $receipt->address_02 = $faker->secondaryAddress;
        $receipt->city = $faker->city;
        $receipt->state = $faker->state;
        $receipt->zip = $faker->postcode;
        $receipt->country = $faker->countryCode;
        $receipt->issued_at = fromLocal('now');
        $receipt->number = $receipt->formatNumber();
        $receipt->amount = 200;
        $receipt->currency_code = (string) currency();

        $receipt->setRelations([
            'template' => $template,
            'lineItems' => collect([
                (object) [
                    'donated_at' => fromUtc('feb 15 last year'),
                    'description' => 'Contribution #XXXXXXX',
                    'amount' => 50,
                    'currency_code' => (string) currency(),
                ],
                (object) [
                    'donated_at' => fromUtc('mar 28 last year'),
                    'description' => 'Contribution #XXXXXXX',
                    'amount' => 150,
                    'currency_code' => (string) currency(),
                ],
            ]),
        ]);

        return $receipt->toPDF();
    }

    public function giftAid()
    {
        $__menu = 'admin.advanced';

        user()->canOrRedirect('giftaid.edit');

        pageSetup('Tax Receipts', 'jpanel');

        return $this->getView('settings/gift_aid', compact('__menu'));
    }

    public function giftAid_save()
    {
        $__menu = 'admin.advanced';

        user()->canOrRedirect('giftaid.edit');

        request()->merge([
            'gift_aid' => request('gift_aid', 0),
        ]);

        if (sys_set()) {
            $this->flash->success('Gift aid settings successfully updated!');
        } else {
            $this->flash->error('There was a problem saving your changes.');
        }

        return redirect()->to('jpanel/settings/gift_aid');
    }

    public function website()
    {
        $__menu = 'admin.advanced';

        user()->canOrRedirect('admin.website');

        pageSetup('Website', 'jpanel');

        return $this->getView('settings/website', [
            '__menu' => $__menu,
            'site' => site(),
        ]);
    }

    public function website_save(MissionControlService $missioncontrol)
    {
        $__menu = 'admin.advanced';

        user()->canOrRedirect('admin.website');

        $values = request()->all([
            'defaultPageTitle',
            '_site_domains',
            'clientDomain',
            'custom_domain_migration_mode',
            'site_password',
            'site_password_message',
            'webStatsPropertyId',
            'web_allow_indexing',
        ]);

        // get active site
        $site = site();

        $new_domains = explode(',', (string) $values['_site_domains']);

        foreach ($new_domains as &$domain) {
            $domain = preg_replace('#^(?:https?:|)//#', '$1', trim($domain, ' /'));

            if (empty($domain)) {
                continue;
            }

            // validate domain name
            if (! preg_match('/^(?:[-A-Za-z0-9]+\.)+[A-Za-z]{2,6}$/', $domain)) {
                $this->flash->error("'$domain' is not a valid domain name.");
                $domain = '';

                continue;
            }

            // dont allow donorshops.com subdomains
            if (preg_match('/donorshops\.com$/', $domain)) {
                $this->flash->error('Usage of the donorshops.com domain and/or subdomains is forbidden.');
                $domain = '';

                continue;
            }

            // dont allow givecloud.co subdomains
            if (preg_match('/givecloud\.co$/', $domain)) {
                $this->flash->error('Usage of the givecloud.co domain and/or subdomains is forbidden.');
                $domain = '';

                continue;
            }

            // dont allow custom domain already in use by other sites
            $found = $missioncontrol->isDomainAlreadyInUse($domain);

            if ($found) {
                $this->flash->error("'$domain' already linked to another site.");
                $domain = '';

                continue;
            }
        }

        $missioncontrol->updateCustomDomains(array_unique(array_filter($new_domains)));

        // remove _site_domains from settings values
        unset($values['_site_domains']);

        // ensure the clientDomain is a valid domain for this site
        if ($values['clientDomain'] !== $site->subdomain && ! $site->custom_domains->contains($values['clientDomain'])) {
            $values['clientDomain'] = $site->subdomain;
        }

        if (! isset($values['custom_domain_migration_mode'])) {
            $values['custom_domain_migration_mode'] = 0;
        }

        $primaryDomainHasChanged = sys_get('clientDomain') !== $values['clientDomain'];

        if (sys_set($values)) {
            $this->flash->success('Website settings successfully updated!');

            if ($primaryDomainHasChanged) {
                dispatch(new BroadcastDomainChange($site));
            }
        } else {
            $this->flash->error('There was a problem saving your changes.');
        }

        return redirect()->to('jpanel/settings/website');
    }

    public function pos()
    {
        $__menu = 'admin.advanced';

        pageSetup('POS Settings', 'jpanel');

        return $this->getView('settings/pos', compact('__menu'));
    }

    public function pos_save()
    {
        $__menu = 'admin.advanced';

        request()->merge([
            'pos_use_default_tax_region' => request('pos_use_default_tax_region', 0),
        ]);

        if (sys_set()) {
            $this->flash->success('POS settings successfully updated!');
        } else {
            $this->flash->error('There was a problem saving your changes.');
        }

        return redirect()->to('jpanel/settings/pos');
    }

    public function email()
    {
        user()->canOrRedirect('email');

        $__menu = 'admin.advanced';

        $system_emails = \Ds\Models\Email::systemNotifications();
        $custom_emails = \Ds\Models\Email::customNotifications();

        pageSetup('Email', 'jpanel');

        return $this->getView('settings/email', compact('__menu', 'system_emails', 'custom_emails'));
    }

    public function email_save(Request $request): RedirectResponse
    {
        user()->canOrRedirect('email');

        try {
            $this->emailSettingsService->update(
                $request->email_from_name,
                $this->emailSettingsService->validateEmailFrom($request->email_from_address),
                $this->emailSettingsService->validateEmailReplyTo($request->email_replyto_address)
            );

            $this->flash->success('Email data successfully updated!');
        } catch (MessageException $e) {
            $this->flash->error($e->getMessage());
        }

        return redirect()->route('backend.settings.email');
    }

    public function sites()
    {
        user()->canOrRedirect('sites');

        $__menu = 'admin.advanced';

        $system_emails = \Ds\Models\Email::systemNotifications();
        $custom_emails = \Ds\Models\Email::customNotifications();

        pageSetup('Sites', 'jpanel');

        return $this->getView('settings/sites', compact('__menu', 'system_emails', 'custom_emails'));
    }

    public function sites_save()
    {
        user()->canOrRedirect('email');

        $__menu = 'admin.advanced';

        if (sys_set()) {
            $this->flash->success('Email data successfully updated!');
        } else {
            $this->flash->error('There was a problem saving your changes.');
        }

        return redirect()->to('jpanel/settings/email');
    }

    public function sponsorship()
    {
        $__menu = 'admin.advanced';

        pageSetup('Sponsorship', 'jpanel');

        return $this->getView('settings/sponsorship', compact('__menu'));
    }

    public function sponsorship_save()
    {
        $__menu = 'admin.advanced';

        $sources = request('sponsorship_sources');

        // force Website to exist as an option for sources
        if (! in_array('Website', $sources)) {
            $sources[] = 'Website';
        }

        request()->merge([
            'sponsorship_sources' => implode(',', $sources),
            'sponsorship_end_reasons' => implode(',', request('sponsorship_end_reasons')),
            'allow_member_to_end_sponsorship' => request('allow_member_to_end_sponsorship', 0),
        ]);

        $previous_sponsorship_num_sponsors = sys_get('sponsorship_num_sponsors');

        if (sys_set()) {
            if ($previous_sponsorship_num_sponsors != sys_get('sponsorship_num_sponsors')) {
                \Ds\Domain\Sponsorship\Models\Sponsorship::updateAllIsSponsored();
            }

            $this->flash->success('Sponsorship settings successfully updated!');
        } else {
            $this->flash->error('There was a problem saving your changes.');
        }

        return redirect()->to('jpanel/settings/sponsorship');
    }

    public function shipping()
    {
        user()->canOrRedirect('shipping.edit');

        pageSetup('Shipping', 'jpanel');

        return $this->getView('settings/shipping', [
            '__menu' => 'admin.advanced',
            'regions' => DB::select('SELECT r.* FROM region r ORDER BY r.country DESC, r.name ASC'),
        ]);
    }

    public function shipping_save()
    {
        $__menu = 'admin.advanced';

        user()->canOrRedirect('shipping.edit');

        request()->merge([
            'shipping_ups_enabled' => request('shipping_ups_enabled', 0),
            'shipping_ups_account' => request('shipping_ups_account'),
            'shipping_ups_negotiated_rates' => request('shipping_ups_negotiated_rates', 0),
            'shipping_fedex_enabled' => request('shipping_fedex_enabled', 0),
            'shipping_usps_enabled' => request('shipping_usps_enabled', 0),
            'shipping_canadapost_enabled' => request('shipping_canadapost_enabled', 0),
            'shipping_taxes_apply' => request('shipping_taxes_apply', 0),
        ]);

        if (sys_set()) {
            $this->flash->success('Shipping data successfully updated!');
        } else {
            $this->flash->error('There was a problem saving your changes.');
        }

        Cache::tags('shipping')->flush();

        return redirect()->to('jpanel/settings/shipping');
    }

    public function save()
    {
        user()->canOrRedirect('admin.advanced');

        if (sys_set()) {
            return redirect()->to('jpanel/settings?s');
        }

        return redirect()->to('jpanel/settings?f');
    }

    public function peerToPeer()
    {
        $__menu = 'admin.advanced';
        pageSetup('Peer-to-Peer', 'jpanel');

        return $this->getView('settings/peer-to-peer', [
        ]);
    }

    public function peerToPeerSave()
    {
        $__menu = 'admin.advanced';

        user()->canOrRedirect('fundraisingpages.edit');

        request()->merge([
            'p2p_enabled' => request('p2p_enabled', 0),
        ]);

        if (sys_set()) {
            $this->flash->success('Peer-to-peer settings successfully updated!');
        } else {
            $this->flash->error('There was a problem saving your changes.');
        }

        return redirect()->to('jpanel/settings/peer-to-peer');
    }
}
