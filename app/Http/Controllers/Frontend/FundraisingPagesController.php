<?php

namespace Ds\Http\Controllers\Frontend;

use Ds\Domain\Theming\Liquid\Drops\FundraisingPageTypeDrop;
use Ds\Http\Requests\FundraisingPageInsertFormRequest;
use Ds\Models\AccountType;
use Ds\Models\FundraisingPage;
use Ds\Models\Member;
use Ds\Models\User;
use Ds\Repositories\AccountTypeRepository;
use Illuminate\Support\Facades\Validator;

class FundraisingPagesController extends Controller
{
    /**
     * Register controller middleware.
     */
    protected function registerMiddleware()
    {
        $this->middleware('auth.member', ['only' => [
            'list',
            'edit',
            'update',
        ]]);

        $this->middleware('requires.feature:fundraising_pages');
    }

    /**
     * Template for creating a peer-to-peer page
     *
     * @return string
     */
    public function list_all()
    {
        // prepare page
        pageSetup('All Fundraisers', 'fundraise');

        $pages = \Ds\Models\FundraisingPage::with('photo', 'teamPhoto');
        $filters = [];

        $pages->activeAndVerified();
        $pages->websiteType();

        // search
        if (request('keywords')) {
            $filters['keywords'] = request('keywords');
            $pages->where('title', 'like', '%' . request('keywords') . '%');
        }

        // new pages
        if (request()->has('new')) {
            $filters['new'] = '';
            $pages->where('activated_date', '>=', toUtc('today -30 days'));

        // ending soon
        } elseif (request()->has('ending-soon')) {
            $filters['ending-soon'] = '';
            $pages->where('goal_deadline', '<=', fromLocal('today +7 days'));

        // recently ended
        } elseif (request()->has('recently-ended')) {
            $filters['recently-closed'] = '';
            $pages->whereIn('status', ['closed', 'active'])
                ->where('goal_deadline', '>=', fromLocal('today -30 days'));

        // pages by category
        } elseif (request('category')) {
            $filters['category'] = request('category');
            $pages->where('category', request('category'))
                ->orderBy('goal_deadline', 'asc');

        // active pages (default)
        } else {
            $pages->orderBy('goal_deadline', 'asc');
        }

        $paged_pages = $pages->paginate(200);

        // render template
        return $this->renderTemplate('fundraisers', [
            'fundraising_pages' => ($paged_pages->count()) ? $paged_pages : [],
            'filter' => $filters,
            'pagination' => get_pagination_data($paged_pages, $filters),
        ]);
    }

    /**
     * Template for creating a peer-to-peer page
     *
     * @return string
     */
    public function list()
    {
        // prepare page
        pageSetup(__('frontend/accounts.fundraisers.index.my_fundraising_pages'), 'fundraise');

        // render view
        return $this->renderTemplate('accounts/fundraisers/index', [
            'fundraising_pages' => member()->fundraisingPages()->with('photo', 'teamPhoto')->get(),
        ]);
    }

    /**
     * View a fundraising page
     *
     * @return string
     */
    public function view($url_slug)
    {
        /** @var \Ds\Models\FundraisingPage $page */
        $page = FundraisingPage::with('product', 'photo', 'paidOrderItems.order', 'teamPhoto', 'product.defaultVariant', 'memberOrganizer')
            ->websiteType()
            ->where('url', $url_slug)
            ->first();

        // if no page found, show a list of fundraisers
        if (! $page) {
            return redirect()->to('fundraisers');
        }

        if (! $page->isViewable()) {
            return redirect()->to('fundraisers');
        }

        // which team member? (for now its just the organizer)
        $team_member = $page->memberOrganizer;

        // prepare page
        pageSetup($page->title, 'fundraise');

        // render view
        return $this->renderTemplate('fundraiser', [
            'fundraising_page' => $page,
            'fundraiser_report_message' => flash('fundraiser_report_message'),
        ]);
    }

    /**
     * Edit a fundraising page
     *
     * @return string
     */
    public function edit(AccountTypeRepository $accountTypeRepository, $url_slug)
    {
        if (! volt_has_account_feature('edit-fundraisers')) {
            abort(404);
        }

        $page = FundraisingPage::with('photo', 'paidOrderItems', 'teamPhoto', 'product.defaultVariant', 'memberOrganizer')
            ->websiteType()
            ->where('url', $url_slug)
            ->where('member_organizer_id', member('id'))
            ->first();

        if (! $page) {
            abort(404);
        }

        // prepare page
        pageSetup(__('frontend/accounts.fundraisers.edit.edit_page', ['name' => $page->title]), 'fundraise');

        // render view
        return $this->renderTemplate('accounts/fundraisers/edit', [
            'fundraising_page' => $page,
            'account_types' => $accountTypeRepository->getOnWebAccountTypeDrops(),
            'recaptcha' => app('recaptcha')->getHtml(),
            'fundraise_message' => flash('fundraise_message'),
            'fundraising_page_types' => $this->_page_type_drops(),
        ]);
    }

    /**
     * Template for creating a peer-to-peer page
     *
     * @return string
     */
    public function create(AccountTypeRepository $accountTypeRepository)
    {
        if (member()->is_denied) {
            return redirect()->to('fundraisers');
        }

        // prepare page
        pageSetup(__('frontend/accounts.fundraisers.edit.create_page'), 'fundraise');

        $page = new FundraisingPage;

        $filters = [];
        if (request()->has('type')) {
            $filters['type'] = request('type');
        }

        // render view
        return $this->renderTemplate('accounts/fundraisers/edit', [
            'account_types' => $accountTypeRepository->getOnWebAccountTypeDrops(),
            'recaptcha' => app('recaptcha')->getHtml(),
            'fundraise_message' => flash('fundraise_message'),
            'fundraising_page_types' => $this->_page_type_drops(),
            'filter' => $filters,
        ]);
    }

    private function _page_type_drops()
    {
        $page_types = [];
        foreach (\Ds\Models\Product::whereAllowFundraisingPages(true)->orderBy('fundraising_page_name')->get() as $product) {
            $page_types[] = new FundraisingPageTypeDrop($product);
        }

        return $page_types;
    }

    /**
     * Save a new fundraising page.
     */
    public function insert(FundraisingPageInsertFormRequest $request)
    {
        // if the user isn't logged in,
        // create an account and log them in
        if (! member_is_logged_in()) {
            // Validate ReCaptcha response
            // if (!app('recaptcha')->verify()) {
            //  flash('fundraise_message', 'Recaptcha failed to validate.');
            //  return redirect()->back();
            // }

            // filter input
            $in = request()->all([
                'account_type_id',
                'organization_name',
                'postal_code',
                'first_name',
                'last_name',
                'email',
                'password',
                'title',
            ]);

            // if no accout type was provided,
            // default it to the default account type
            if (! $in['account_type_id']) {
                if ($default_account_type = AccountType::getDefault()) {
                    $in['account_type_id'] = $default_account_type->id;
                } else {
                    flash('fundraise_message', __('frontend/accounts.fundraisers.no_default_account_type'));

                    return redirect()->back();
                }
            }

            // assume we don't need to force organization name
            $force_organization_name = false;

            // look up the provided account type and check to
            // see if its an organization
            $account_type = AccountType::find($in['account_type_id']);
            if ($account_type) {
                $force_organization_name = $account_type->is_organization;
            }

            $validator = Validator::make($in, [
                'reference_id' => 'nullable|numeric',
                'account_type_id' => 'required|numeric|exists:account_types,id',
                'organization_name' => ($force_organization_name) ? 'required' : 'nullable',
                'postal_code' => 'required',
                'first_name' => 'required',
                'last_name' => 'required',
                'email' => 'required|unique:member|email',
                'password' => 'required',
            ]);

            // failed validation
            if ($validator->fails()) {
                $error = $validator->errors()->first();
                if ($error == 'The email has already been taken.') {
                    $error = __('frontend/accounts.fundraisers.email_already_registered', ['email' => $in['email']]);
                }
                flash('fundraise_message', $error);

                return redirect()->back();
            }

            // create an account
            $member = Member::register([
                'title' => $in['title'],
                'first_name' => $in['first_name'],
                'last_name' => $in['last_name'],
                'bill_organization_name' => $in['organization_name'],
                'account_type_id' => $in['account_type_id'],
                'email' => $in['email'],
                'password' => bcrypt($in['password']),
                'bill_zip' => $in['postal_code'],
                'ship_zip' => $in['postal_code'],
            ], true);
        }

        $page = new FundraisingPage();
        $page->guidelines_accepted_at = toUtc('now');

        // create the page
        $page = $this->_savePage($page);

        // publish the page (sets the status and sends notifications)
        $page->markAsPendingOrActivate();

        // go to the fundraiser
        return redirect()->to($page->absolute_url);
    }

    /**
     * Save page
     *
     * @param \Ds\Models\FundraisingPage $page
     */
    private function _savePage($page)
    {
        $page->member_organizer_id = member('id');
        $page->product_id = request('page_type_id');
        $page->title = request('page_name');
        $page->category = request('category');
        $page->currency_code = request('currency_code', sys_get('dpo_currency'));
        $page->goal_deadline = request('goal_deadline');
        $page->goal_amount = numeral(request('goal_amount'))->toFloat();
        $page->is_team = (request()->input('is_team') == 1) ? true : false;
        $page->team_name = ($page->is_team) ? request('team_name') : null;
        $page->video_url = request('video');

        request()->whenHas('content', fn ($value) => $page->description = $value);
        request()->whenFilled('description_template', fn ($value) => $page->description_template = $value);

        $page->save();

        if (array_key_exists('currency_code', $page->getChanges())) {
            $page->updateAggregates();
        }

        if (request('page_photo') == 'custom' && request()->hasFile('page_photo_custom') && request()->file('page_photo_custom')->isValid()) {
            if ($page_photo_custom = \Ds\Models\Media::storeUpload('page_photo_custom', ['collection_name' => 'fundraisers'])) {
                $page->photo_id = $page_photo_custom->id;
                $page->save();
            }
        } elseif (request()->hasFile('page_photo') && request()->file('page_photo')->isValid()) {
            if ($pagePhoto = \Ds\Models\Media::storeUpload('page_photo', ['collection_name' => 'fundraisers'])) {
                $page->photo_id = $pagePhoto->id;
                $page->save();
            }
        } elseif (request('page_photo') && is_numeric(request('page_photo'))) {
            if ($pagePhoto = \Ds\Models\Media::find(request('page_photo'))) {
                $page->photo_id = $pagePhoto->id;
                $page->save();
            }
        }

        if ($page->is_team && request()->hasFile('team_photo') && request()->file('team_photo')->isValid()) {
            if ($team_photo = \Ds\Models\Media::storeUpload('team_photo')) {
                $page->team_photo_id = $team_photo->id;
                $page->save();
            }
        }

        // publish the page (sets the status and sends notifications)
        if ($page->status == 'suspended') {
            $page->markAsPendingOrActivate();
        }

        if ($page->is_team && request()->hasFile('team_photo') && request()->file('team_photo')->isValid()) {
            if ($team_photo = \Ds\Models\Media::storeUpload('team_photo')) {
                $page->team_photo_id = $team_photo->id;
                $page->save();
            }
        }

        return $page;
    }

    /**
     * Update an existing fundraising page.
     */
    public function update(FundraisingPageInsertFormRequest $request, $url_slug)
    {
        if (! volt_has_account_feature('edit-fundraisers')) {
            abort(404);
        }

        $page = FundraisingPage::where('url', $url_slug)
            ->websiteType()
            ->where('member_organizer_id', member('id'))
            ->first();

        if (! $page || ! member()) {
            abort(404);
        }

        // create the page
        $this->_savePage($page);

        // Notify Staff
        User::where('notify_fundraising_page_edited', '=', true)->get()->each(function ($user) use ($page) {
            $user->mail(new \Ds\Mail\FundraisingPageEdited($page->getMergeTags()));
        });

        // go to the fundraiser
        return redirect()->to($page->absolute_url);
    }

    /**
     * Report a fundraising page.
     */
    public function cancel($url_slug)
    {
        $page = FundraisingPage::where('url', $url_slug)
            ->where('member_organizer_id', member('id'))
            ->activeOrPendingForLoggedInSupporter()
            ->websiteType()
            ->first();

        if (! $page || ! member()) {
            abort(404);
        }

        // report the page
        $page->cancel();

        // go to the fundraiser list
        return redirect()->to('/fundraisers');
    }

    /**
     * Report a fundraising page.
     */
    public function report($url_slug)
    {
        $page = FundraisingPage::where('url', $url_slug)
            ->where('status', 'active')
            ->websiteType()
            ->first();

        if (! $page) {
            abort(404);
        }

        // report the page
        $page->report(request()->input('member_id'), request()->input('reason'));

        flash('fundraiser_report_message', __('frontend/accounts.fundraisers.page_reported'));

        // go to the fundraiser
        return redirect()->to($page->absolute_url);
    }
}
