<?php

namespace Ds\Http\Controllers;

use Carbon\Carbon;
use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Domain\Sponsorship\Models\PaymentOptionGroup;
use Ds\Http\Controllers\Frontend\API\CheckoutsController;
use Ds\Models\Order;
use Ds\Models\PaymentMethod;
use Ds\Models\Product;
use Ds\Models\ProductCategory;
use Ds\Models\PromoCode;
use Ds\Services\GivecloudCoreConfigRepository;
use Illuminate\Support\Str;
use Throwable;

class POSController extends Controller
{
    /**
     * View main POS layout
     */
    public function index(GivecloudCoreConfigRepository $coreRepo)
    {
        user()->canOrRedirect('pos.edit');

        $config = (object) $coreRepo->getConfig();
        $gateways = $coreRepo->getGateways();

        $groups = PaymentOptionGroup::with(['options' => function ($qry) {
            $qry->where('is_custom', false);
        }])->get();

        if (dpo_is_enabled()) {
            $dp_fields = [
                'gl' => 'General Ledger',
                'campaign' => 'Campaign',
                'solicit_code' => 'Solicitation',
                'sub_solicit_code' => 'Sub Solicitation',
                'gift_type' => 'Gift Type',
                'ty_letter_no' => 'TY Letter Code',
                'fair_market_value' => 'Fair Mkt. Value',
                'gift_narrative' => 'Gift Memo',
                'acknowledgepref' => 'Acknowledge Preference',
            ];

            if (sys_get('dp_use_nocalc')) {
                $dp_fields['no_calc'] = 'NoCalc';
            }

            $dp_udfs = [];
            foreach ([9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23] as $ix) {
                if (sys_get('dp_meta' . $ix . '_field')) {
                    $dp_udfs[sys_get('dp_meta' . $ix . '_field')] = sys_get('dp_meta' . $ix . '_label');
                }
            }

            $dp_codes = [];
            foreach (array_merge($dp_fields, $dp_udfs) as $field => $label) {
                try {
                    if ($field == 'gl') {
                        $dp_codes['gl'] = app('Ds\Services\DonorPerfectService')->getCodes('gl_code');
                    } else {
                        $dp_codes[$field] = app('Ds\Services\DonorPerfectService')->getCodes($field);
                    }
                } catch (Throwable $e) {
                    // do nothing
                }
            }
        }

        // return view
        return view('pos.index', [
            'config' => $config,
            'gateways' => $gateways,
            'payment_option_groups' => $groups,
            'product_bookmarks' => $this->_getBookmarks(),
            'account_types' => \Ds\Models\AccountType::all(),
            'dp_fields' => $dp_fields ?? [],
            'dp_udfs' => $dp_udfs ?? [],
            'dp_codes' => $dp_codes ?? [],
        ]);
    }

    /**
     * Get the POS config.
     *
     * @return array
     */
    public function getConfig()
    {
        user()->canOrRedirect('pos.edit');

        return [
            'sources' => array_map('trim', explode(',', sys_get('pos_sources'))),
        ];
    }

    /**
     * Get accounts.
     *
     * @return array
     */
    public function getAccounts()
    {
        user()->canOrRedirect('pos.edit');

        $accounts = [];

        $query = \Ds\Models\Member::active()
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->orderBy('email')
            ->orderByDesc('last_payment_at');

        request()->whenFilled('alpha', function ($value) use ($query) {
            $query->where('display_name', 'like', "$value%");
        });

        request()->whenFilled('query', function ($value) use ($query) {
            $query->where(function ($query) use ($value) {
                $query->where('display_name', 'like', "%$value%");
                $query->orWhere('email', 'like', "%$value%");
                $query->orWhere('bill_address_01', 'like', "%$value%");
                $query->orWhere('bill_address_02', 'like', "%$value%");
                $query->orWhere('bill_city', 'like', "%$value%");
                $query->orWhere('bill_state', 'like', "%$value%");
                $query->orWhere('bill_zip', 'like', "%$value%");
                $query->orWhere('bill_country', 'like', "%$value%");
                $query->orWhere('bill_phone', 'like', "%$value%");
                $query->orWhere('donor_id', 'like', "%$value%");
            });

            $query->take(25);
        });

        $retrievalMethod = 'cursor';
        $today = toLocalFormat('today', 'date');

        // only include membership data for
        // subscribers with less than X supporters
        $includeMembershipInfo = $query->count() < 5000;

        if ($includeMembershipInfo) {
            $retrievalMethod = $query->getQuery()->limit ? 'get' : 'lazy';
            $query->with('groups');
        }

        foreach ($query->{$retrievalMethod}() as $member) {
            $group = null;

            // more performant version of the $group->membership accessor. doing a
            // string comparison of the dates instead of using the carbon lte/gte is ~2x faster
            if ($includeMembershipInfo) {
                $group = $member->groups->sortByDesc(function ($group) use ($today) {
                    $startDate = $group->pivot->getRawOriginal('start_date') ?? $today;
                    $endDate = $group->pivot->getRawOriginal('end_date') ?? $today;

                    return [$startDate <= $today && $endDate >= $today, $startDate];
                })->first();
            }

            $membershipExpiresOn = optional($group->pivot ?? null)->getRawOriginal('end_date') ?: null;
            $membershipIsExpired = $membershipExpiresOn ? $membershipExpiresOn < $today : null;

            $accounts[] = [
                'id' => $member->id,
                'display_name' => $member->display_name,
                'email' => $member->email,
                'billing_address' => $member->display_bill_address,
                'billing_phone' => $member->display_bill_phone,
                'membership_name' => $group->name ?? null,
                'membership_expires_on' => $membershipExpiresOn,
                'membership_is_expired' => $membershipIsExpired,
                'donor_id' => $member->donor_id,
            ];
        }

        return $accounts;
    }

    /**
     * Get discounts.
     *
     * @return \Illuminate\Support\Collection
     */
    public function getDiscounts()
    {
        user()->canOrRedirect('pos.edit');

        return \Ds\Models\PromoCode::query()
            ->notExpired()
            ->orderBy('code')
            ->pluck('code');
    }

    /**
     * POS product search
     *
     * /pos/product.json?keywords=Canvas Print
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchProducts()
    {
        user()->canOrRedirect('pos.edit');

        // search for products
        $products = Product::with(['variants', 'defaultVariant', 'customFields'])->activeForPOS();

        // if search
        if (request()->filled('keywords')) {
            $products->where(function ($q) {
                $q->where('code', 'like', '%' . request('keywords') . '%')
                    ->orWhere('name', 'like', '%' . request('keywords') . '%')
                    ->orWhereRaw('exists (select 1 from productinventory where productid = product.id and productinventory.variantname LIKE ?)', ['%' . request('keywords') . '%']);
            });
        }

        // if category list
        if (request()->filled('category_id')) {
            $products->whereIn('id', function ($query) {
                $query->select('productid')
                    ->from('productcategorylink')
                    ->where('categoryid', request('category_id'));
            });
        }

        $products = $products->get();

        $defaultOrderBy = Str::snake(strtolower(sys_get('category_default_order_by')));

        if ($defaultOrderBy == 'filter') {
            $sortBy = 'author';
        } elseif ($defaultOrderBy == 'price') {
            $sortBy = 'actualprice';
        } elseif ($defaultOrderBy == 'price_desc') {
            $sortBy = 'actualprice_desc';
        } elseif ($defaultOrderBy == 'category_name') {
            $sortBy = 'categoryname';
        } else {
            $sortBy = $defaultOrderBy;
        }

        if (substr($sortBy, -4) === 'desc') {
            $products = $products->sortByDesc(substr($sortBy, 0, -5))->values();
        } else {
            $products = $products->sortBy($sortBy)->values();
        }

        $convertCurrency = function ($product, $amount) {
            return money($amount, $product->base_currency)
                ->toCurrency(request('currency_code'))
                ->getAmount();
        };

        $products->each(function ($product) use ($convertCurrency) {
            if ($product->defaultVariant) {
                $product->defaultVariant->price = $convertCurrency($product, $product->defaultVariant->price);
                $product->defaultVariant->saleprice = $convertCurrency($product, $product->defaultVariant->saleprice);
                $product->defaultVariant->actual_price = $convertCurrency($product, $product->defaultVariant->actual_price);
            }
            foreach ($product->variants as $variant) {
                $variant->price = $convertCurrency($product, $variant->price);
                $variant->saleprice = $convertCurrency($product, $variant->saleprice);
                $variant->actual_price = $convertCurrency($product, $variant->actual_price);
            }
        });

        // return json data
        return response()->json($products);
    }

    /**
     * List categories
     *
     * /pos/categories.json
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function listCategories()
    {
        user()->canOrRedirect('pos.edit');

        // search for products
        $categories = ProductCategory::with('childCategories.childCategories.childCategories.childCategories.childCategories.childCategories.childCategories')
            ->whereNull('parent_id')
            ->orderBy('sequence')
            ->get();

        // private function
        $this->_collect_images($categories);

        // return json data
        return response()->json($categories);
    }

    // collect top 5 product thumbs
    private function _collect_images($cats)
    {
        foreach ($cats as $cat) {
            $cat->product_thumbs = Product::select('media_id')
                ->join('productcategorylink', 'productcategorylink.productid', '=', 'product.id', 'inner')
                ->where('productcategorylink.categoryid', '=', $cat->id)
                ->where('product.show_in_pos', '=', 1)
                ->whereNotNull('product.media_id')
                ->limit(4)
                ->orderBy('product.id', 'desc')
                ->pluck('media_id');

            // set proper url
            foreach ($cat->product_thumbs as $ix => $thumb) {
                $cat->product_thumbs[$ix] = media_thumbnail($thumb);
            }

            if (count($cat->childCategories) > 0) {
                $this->_collect_images($cat->childCategories);
            }
        }
    }

    /**
     * New cart/order
     */
    public function newOrder()
    {
        user()->canOrRedirect('pos.edit');

        // create order
        $order = Order::newPOSOrder();

        if (request()->filled('bill_country')) {
            $this->updateOrder($order);
        } elseif (request()->has('member_id')) {
            $order->populateMember(request('member_id'));
        }

        $order->source = request('source', $order->source);
        $order->referral_source = request('referral_source', $order->referral_source);
        $order->comments = request('comments', $order->comments);
        $order->is_anonymous = request('is_anonymous', $order->is_anonymous);
        $order->ordered_at = request('ordered_at', $order->ordered_at);
        $order->dcc_enabled_by_customer = request('dcc_enabled_by_customer', $order->dcc_enabled_by_customer);
        $order->currency_code = request('currency_code', $order->currency_code);
        $order->save();

        // workaround POS app v0.0.8 doesn't prefill
        // the pos tax juristication using the defaults
        if (
               ! request('tax_address1')
            && ! request('tax_address2')
            && ! request('tax_city')
            && ! request('tax_state')
            && ! request('tax_zip')
            && ! request('tax_country')
        ) {
            $order->tax_address1 = sys_get('pos_tax_address1');
            $order->tax_address2 = sys_get('pos_tax_address2');
            $order->tax_city = sys_get('pos_tax_city');
            $order->tax_state = sys_get('pos_tax_state');
            $order->tax_zip = sys_get('pos_tax_zip');
            $order->tax_country = sys_get('pos_tax_country');
            $order->save();

            $order->calculate();
        }

        // return json order
        return $this->_orderResponse($order->id);
    }

    /**
     * Update an attribute of the cart/order
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateOrder(Order $order)
    {
        user()->canOrRedirect('pos.edit');

        if (request()->has('currency_code')) {
            $order->currency_code = request('currency_code');
            $order->save();

            return $this->_orderResponse($order->id);
        }

        if (request()->has('send_confirmation_emails')) {
            $order->send_confirmation_emails = request('send_confirmation_emails');
            $order->save();

            return $this->_orderResponse($order->id);
        }

        // some flags we need to track while updating the order
        $did_ship_address_change = false;
        $did_bill_address_change = false;
        $did_bill_email_change = false;
        $did_member_change = false;

        // update the rest of the order attributes
        $order->billing_title = request('bill_title');
        $order->billing_first_name = request('bill_first_name');
        $order->billing_last_name = request('bill_last_name');
        $order->billing_organization_name = request('bill_organization_name');
        $order->billingaddress1 = request('bill_address');
        $order->billingaddress2 = request('bill_address2');
        $order->billingcity = request('bill_city');
        $order->billingstate = request('bill_state');
        $order->billingzip = request('bill_zip');
        $order->billingcountry = request('bill_country');
        $order->billingphone = request('bill_phone');
        $order->billingemail = request('bill_email');
        $order->shipping_title = request('ship_title');
        $order->shipping_first_name = request('ship_first_name');
        $order->shipping_last_name = request('ship_last_name');
        $order->shipping_organization_name = request('ship_organization_name');
        $order->shipemail = request('ship_email');
        $order->shipaddress1 = request('ship_address');
        $order->shipaddress2 = request('ship_address2');
        $order->shipcity = request('ship_city');
        $order->shipstate = request('ship_state');
        $order->shipzip = request('ship_zip');
        $order->shipcountry = request('ship_country');
        $order->shipphone = request('ship_phone');
        $order->courier_method = request()->filled('courier_method') ? request('courier_method') : null;
        $order->shipping_method_id = request()->filled('shipping_method_id') ? request('shipping_method_id') : null;
        $order->updated_by = user('id');
        $order->updated_at = now();
        $order->ordered_at = toUtc(request('ordered_at'));
        $order->source = request('source');
        $order->tax_address1 = request('tax_address1');
        $order->tax_address2 = request('tax_address2');
        $order->tax_city = request('tax_city');
        $order->tax_state = request('tax_state');
        $order->tax_zip = request('tax_zip');
        $order->tax_country = request('tax_country');
        $order->is_free_shipping = (request('is_free_shipping') == 1);
        $order->referral_source = (request('other_referral_source')) ? request('other_referral_source') : request('referral_source');
        $order->account_type_id = request('account_type_id');
        $order->email_opt_in = request('email_opt_in', false);
        $order->comments = request('special_notes');
        $order->is_anonymous = request('is_anonymous');
        $order->dcc_enabled_by_customer = request('dcc_enabled_by_customer');
        $order->dcc_type = request('dcc_type');

        $did_ship_address_change = $order->isDirty('shipaddress1')
            || $order->isDirty('shipaddress2')
            || $order->isDirty('shipcity')
            || $order->isDirty('shipstate')
            || $order->isDirty('shipzip')
            || $order->isDirty('shipcountry');

        $did_bill_address_change = $order->isDirty('billingaddress1')
            || $order->isDirty('billingaddress2')
            || $order->isDirty('billingcity')
            || $order->isDirty('billingstate')
            || $order->isDirty('billingzip')
            || $order->isDirty('billingcountry');

        $did_bill_email_change = $order->isDirty('billingemail');

        $order->save();

        // if we are changing the member
        if (request('member_id', 0) > 0 && $order->member_id != request('member_id')) {
            $order->populateMember(request('member_id'));

            // addresses changed
            $did_ship_address_change = true;
            $did_bill_address_change = true;
            $did_bill_email_change = true;
            $did_member_change = true;

        // if we are clearing the member
        } elseif (! request('member_id') && $order->member_id != null) {
            $order->unpopulateMember();

            // addresses changed
            $did_ship_address_change = true;
            $did_bill_address_change = true;
            $did_bill_email_change = true;
            $did_member_change = true;
        }

        // INTENDED TAX BEHAVIOUR
        // - IF default to member's address (pos_use_default_tax_region == true)
        //    - we set tax address to shipping address
        //    - IF shipping is not valid, use billing address
        //    - IF billing address is not valid, use tax address
        //    - IF the tax address is manually overriden, use the tax address
        // - OTHERWISE
        //    - always use tax address
        //
        // ... and also, Psalm 18:2 - God is my rock. This code shall not fail.

        // if tax address was NOT manually changed AND we need to use the customer's tax region
        if (($did_ship_address_change || $did_bill_address_change) && sys_get('pos_use_default_tax_region')) {
            // check validity of both shipping & billing address to determine
            // whether or not they are even usable for tax purposes
            $has_valid_shipping_address = $order->shipaddress1 && $order->shipcity && $order->shipstate && $order->shipzip;
            $has_valid_billing_address = $order->billingaddress1 && $order->billingcity && $order->billingstate && $order->billingzip;

            // f, t
            // dump([$order->shipaddress1, $order->shipcity, $order->shipstate, $order->shipzip]);
            // dump([$order->billingaddress1, $order->billingcity, $order->billingstate, $order->billingzip]);
            // dump([$has_valid_shipping_address, $has_valid_billing_address, $did_ship_address_change, $did_bill_address_change]);

            // if shipping exists and was changed, use it
            if ($did_ship_address_change && $has_valid_shipping_address) {
                $order->tax_address1 = $order->shipaddress1;
                $order->tax_address2 = $order->shipaddress2;
                $order->tax_city = $order->shipcity;
                $order->tax_state = $order->shipstate;
                $order->tax_zip = $order->shipzip;
                $order->tax_country = $order->shipcountry;
                $order->save();

            // if shipping doesn't exist, bill address exists, use it
            } elseif (! $has_valid_shipping_address && $has_valid_billing_address && $did_bill_address_change) {
                $order->tax_address1 = $order->billingaddress1;
                $order->tax_address2 = $order->billingaddress2;
                $order->tax_city = $order->billingcity;
                $order->tax_state = $order->billingstate;
                $order->tax_zip = $order->billingzip;
                $order->tax_country = $order->billingcountry;
                $order->save();
            }
        }

        // if the key identifiers of a unique person have
        // changed (billing email or logged in member),
        // we need to re-apply promos that have per-person
        // limits
        if ($did_member_change || $did_bill_email_change) {
            $validation_messages = $order->revalidatePerAccountPromos();
        }

        // recalculate incase the data changed above
        $order->calculate();
        $order->reapplyPromos();

        // return json order
        if (! empty($validation_messages)) {
            return $this->_orderResponse($order->id, 'error', implode(' ', $validation_messages));
        }

        return $this->_orderResponse($order->id);
    }

    /**
     * Complete the order
     */
    public function completeOrder(Order $order)
    {
        // exception for donation kiosk (i.e. iOS kiosk) contributions
        // https://givecloud.atlassian.net/browse/GCLD-2711
        if (! user()->can('pos.edit') && $order->source === 'Kiosk') {
            return $this->_orderResponse($order->id);
        }

        user()->canOrRedirect('pos.edit');

        if ($order->is_paid) {
            if (request('signature')) {
                $payment = $order->successfulPayments->first();
                if ($payment) {
                    $payment->signature = request('signature');
                    $payment->save();
                }
            }

            if (request('mark_as_complete') == 1) {
                $order->iscomplete = true;
                $order->save();
            }

            $order->unlock();

            return $this->_orderResponse($order->id);
        }

        // if send_confirmation_emails
        if (request('send_confirmation_emails') === '0') {
            $order->send_confirmation_emails = false;
            $order->save();
        }

        // complete the order
        try {
            // if payment method was provided, we need to charge it
            if (request('payment_method_id')) {
                $payment_method = PaymentMethod::where('member_id', $order->member_id)->find(request('payment_method_id'));

                if (! $payment_method) {
                    throw new MessageException('Payment method does not exist.');
                }

                $order->payment_type = 'payment_method';
                $order->payment_method_id = $payment_method->id;
                $order->payment_provider_id = $payment_method->payment_provider_id;
                $order->save();

                $res = (new CheckoutsController)->chargeToken($order);

                if ($res->getStatusCode() > 302) {
                    throw new MessageException(data_get($res->getOriginalContent(), 'error'));
                }

                $order->unlock();
            } else {
                // process payment
                $order->processPayment([
                    // required params
                    'payment_type' => request('payment_type', 'free'),
                    'payment_at' => request('payment_at', now()),

                    // conditional cash params
                    'cash_received' => request('cash_received', null),
                    'cash_change' => request('cash_change', null),

                    // conditional check params
                    'check_amt' => request('check_amt', null),
                    'check_number' => request('check_number', null),
                    'check_date' => (trim(request('check_date')) !== '') ? Carbon::createFromFormat('M d, Y', request('check_date')) : null,

                    // conditional other params
                    'payment_other_reference' => request('payment_other_reference', null),
                    'payment_other_note' => request('payment_other_note', null),

                    // conditional cc params
                    'cc_type' => request('cc_type', null),
                    'cc_last_four' => request('cc_last_four', null),
                    'cc_expiry_month' => request('cc_expiry_month', null),
                    'cc_expiry_year' => request('cc_expiry_year', null),
                    'cc_transaction_id' => request('cc_transaction_id', null),
                    'cc_response_text' => request('cc_response_text', null),
                ]);
            }

            // if mark_as_complete
            if (request()->input('mark_as_complete') == 1) {
                $order->iscomplete = true;
                $order->save();
            }

            // return json order
            return $this->_orderResponse($order->id);
            // catch an error
        } catch (\Exception $e) {
            // return json order
            return $this->_orderResponse($order->id, 'error', $e->getMessage());
        }
    }

    /**
     * Add an item to the cart/order
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function addItem(Order $order)
    {
        user()->canOrRedirect('pos.edit');

        // add item
        try {
            $order->addItem([
                // required params
                'variant_id' => request('variant_id'),

                // optional params
                'qty' => request('qty', 1),
                'amt' => request('amount', 0),
                'recurring_frequency' => request('recurring_frequency', null),
                'recurring_with_initial_charge' => request('recurring_with_initial_charge', null),
                'recurring_day' => request('recurring_day', null),
                'recurring_day_of_week' => request('recurring_day_of_week', null),
                'fields' => request('fields', []),
                'gl_code' => request('gl_code'),
                'gift_aid' => (bool) request('gift_aid'),
                'metadata' => request('metadata', []),
            ]);
        } catch (\Exception $e) {
            return $this->_orderResponse($order->id, 'error', $e->getMessage());
        }

        // return json order
        return $this->_orderResponse($order->id);
    }

    /**
     * Add an child to the cart/order
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function addByChildReference(Order $order)
    {
        user()->canOrRedirect('pos.edit');

        try {
            $sponsorship = \Ds\Domain\Sponsorship\Models\Sponsorship::where('reference_number', request()->input('reference_number'))->first();

            if (! $sponsorship) {
                throw new MessageException("Child reference '" . request()->input('reference_number') . "' not found.");
            }

            $item = $order->addSponsorship([
                'sponsorship_id' => $sponsorship->id,
                'payment_option_id' => request()->input('payment_option_id'),
                'initial_charge' => (request()->input('recurring_with_initial_charge') == 1),
            ]);

            return $this->_orderResponse($order->id);
        } catch (\Exception $e) {
            return $this->_orderResponse($order->id, 'error', $e->getMessage());
        }
    }

    /**
     * Add an child to the cart/order
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function addByFundraisingPage(Order $order)
    {
        user()->canOrRedirect('pos.edit');

        $fundraising_page = \Ds\Models\FundraisingPage::with('product.variants')
            ->websiteType()
            ->find(request('fundraising_page_id'));

        if (! $fundraising_page) {
            return $this->_orderResponse($order->id, 'error', "Fundraising page id '" . request('fundraising_page_id') . "' not found.");
        }

        /* USE THIS IN AFFINITY
        $one_time_variant = $fundraising_page->product
            ->variants
            ->filter(function($v){
                return $v->billing_period == 'onetime';
            })
            ->first();

        if (!$one_time_variant) {
            return $this->_orderResponse($order->id, 'error', "'{$fundraising_page->title}' doesn't accept one-time payments. Check the variant settings for the '{$fundraising_page->product->name}' fundraising item.");
        }
        */

        // in CDN, we're just using the default variant on the product
        // and there's no need to check for one-time / recurring
        $one_time_variant = $fundraising_page->product
            ->variants
            ->filter(function ($v) {
                return $v->isdefault;
            })->first();

        if (! $one_time_variant) {
            return $this->_orderResponse($order->id, 'error', "'{$fundraising_page->title}' has no default variant. Check the variant settings for the '{$fundraising_page->product->name}' fundraising item.");
        }

        // add item
        try {
            $order->addItem([
                'variant_id' => $one_time_variant->id,
                'fundraising_page_id' => $fundraising_page->id,
                'amt' => request('amount', 0),
                'gl_code' => request('gl_code'),
            ]);

            $order->comments = request('comments');
            $order->is_anonymous = request('is_anonymous');
            $order->save();
        } catch (\Exception $e) {
            return $this->_orderResponse($order->id, 'error', $e->getMessage());
        }

        // return json order
        return $this->_orderResponse($order->id);
    }

    /**
     * Remove an item from the cart/order
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function removeItem(Order $order)
    {
        user()->canOrRedirect('pos.edit');

        // remove the order item
        $order->removeItem(request('order_item_id'));

        // return json order
        return $this->_orderResponse($order->id);
    }

    /**
     * Apply a promocode
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function applyPromos(Order $order)
    {
        user()->canOrRedirect('pos.edit');

        // clear existing
        $order->clearPromos();

        // prepare to apply a new list of promos
        $messages = [];

        // bail if no codes
        if (! request()->filled('promocodes')) {
            return $this->_orderResponse($order->id, 'success', 'Promo codes cleared.');
        }

        // make sure we're dealing w/ an array of codes
        if (is_string(request('promocodes'))) {
            $promo_codes = explode(',', request('promocodes'));
        } else {
            $promo_codes = request('promocodes');
        }

        // go over the list of promos
        foreach ($promo_codes as $promocode) {
            // try safely applying a promocode
            try {
                // validate this promo
                if (PromoCode::validate($promocode, true, $order->billingemail, $order->member)) {
                    // apply the promo code
                    $applied_codes = $order->applyPromos($promocode);

                    // if no codes were applied, throw an exception
                    if (count($applied_codes) == 0) {
                        throw new MessageException('We could not apply ' . $promocode . ' to any of the items.');
                    }
                }

                // if an error happens trying to apply a code
            } catch (Throwable $e) {
                // return json error
                $messages[] = $e->getMessage();
            }
        }

        // return json success
        if (count($messages) > 0) {
            return $this->_orderResponse($order->id, 'error', implode(',', $messages));
        }

        return $this->_orderResponse($order->id, 'success', 'Promo codes applied.');
    }

    /**
     * PRIVATE
     * Centralize the order response (lots of 'with' statements)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    private function _orderResponse($order_id, $status = null, $message = null)
    {
        return response()->json([
            'status' => $status,
            'message' => $message,
            'order' => Order::with('items.variant.product', 'items.metadataRelation', 'items.fundraisingPage', 'items.sponsorship', 'promocodes', 'member.accountType', 'member.paymentMethods', 'shippingMethod')->find($order_id),
        ]);
    }

    public function paymentReponse()
    {
        return response('<textarea>' . request('token-id') . '</textarea>');
    }

    /**
     * Remove a bookmark
     *
     * Requires POST product_id
     *
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    public function removeBookmark()
    {
        user()->canOrRedirect('pos.edit');

        // get the bookmarked products
        $product_ids = collect(user()->metadata->pos_bookmarked_product_ids ?? []);

        // remove the product_id passed in
        $product_ids = $product_ids->reject(function ($id) {
            return $id == request('product_id');
        });

        // save the model
        user()->metadata(['pos_bookmarked_product_ids' => $product_ids->all()]);
        user()->save();

        return $this->_getBookmarks();
    }

    /**
     * Add a bookmark
     *
     * Requires POST product_id
     *
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    public function addBookmark()
    {
        user()->canOrRedirect('pos.edit');

        // get the bookmarked products
        $product_ids = collect(user()->metadata->pos_bookmarked_product_ids ?? []);

        // add bookmark
        $product_ids->push(request('product_id'));

        // save the model
        user()->metadata(['pos_bookmarked_product_ids' => $product_ids->unique()->all()]);
        user()->save();

        return $this->_getBookmarks();
    }

    /**
     * Get a collection of product bookmarks
     *
     * @return \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection
     */
    private function _getBookmarks()
    {
        if ($ids = user()->metadata->pos_bookmarked_product_ids ?? []) {
            return \Ds\Models\Product::with(['variants', 'defaultVariant', 'customFields'])
                ->whereIn('id', $ids)
                ->where('show_in_pos', true)
                ->get();
        }

        return collect([]);
    }
}
