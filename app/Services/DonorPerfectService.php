<?php

namespace Ds\Services;

use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Enums\ExternalReference\ExternalReferenceType;
use Ds\Enums\MemberOptinSource;
use Ds\Models\AccountType;
use Ds\Models\Email;
use Ds\Models\GroupAccountTimespan;
use Ds\Models\Member;
use Ds\Models\Order;
use Ds\Models\OrderItem;
use Ds\Models\Product;
use Ds\Models\RecurringPaymentProfile;
use Ds\Models\Transaction;
use Ds\Services\DonorPerfect\SyncableItemsService;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Swift_Validate;
use Throwable;

class DonorPerfectService
{
    protected SyncableItemsService $syncableItemsService;

    public function __construct(SyncableItemsService $syncableItemsService)
    {
        $this->syncableItemsService = $syncableItemsService;
    }

    /**
     * Ping DP with a simple select statement that
     * confirms the user exists and also tests
     * connectivity.
     *
     * CACHED by use/pass for 10 mins
     *
     * @param bool $force
     * @return bool
     */
    public function ping($force = false)
    {
        // cache key to use to cache the ping request
        $key = 'dp-ping-' . app('dpo.client')->getAuthFingerpint();

        // clear the cache if the force flag is passed
        if ($force) {
            Cache::forget($key);
        }

        // run and cache ping (10min)
        $ping = Cache::remember($key, now()->addMinutes(10), function () {
            try {
                return count(dpo_request('SELECT TOP 1 user_id FROM dpuser')) === 1;
            } catch (Throwable $e) {
                return false;
            }
        });

        // if the ping failed, clear the cache
        if ($ping === false) {
            Cache::forget($key);
        }

        // return the ping (true/false)
        return $ping;
    }

    /**
     * Push an Account to DonorPerfect
     *
     * @param \Ds\Models\Member $member
     * @return \stdClass|bool
     */
    public function pushAccount(Member $member)
    {
        // bail if dp is not connected
        if (! dpo_is_connected()) {
            throw new MessageException('DonorPerfect is not connected.');
        }

        // compile contact data
        $donor_data = [
            'title' => $member->title,
            'first_name' => $member->first_name,
            'last_name' => $member->last_name,
            'email' => $member->email ?? $member->bill_email,
            'address' => $member->bill_address_01,
            'address2' => $member->bill_address_02,
            'city' => $member->bill_city,
            'state' => $member->bill_state,
            'zip' => $member->bill_zip,
            'country' => $member->bill_country,
            'phone' => $member->bill_phone,
            'organization' => ($member->accountType && $member->accountType->is_organization) ? $member->bill_organization_name : null,
            'donor_type' => ($member->accountType && $member->accountType->dp_code) ? $member->accountType->dp_code : null,
            'no_email' => 'N',
            // 'udfs'         => $this->metaUdfsForDonor($member)
        ];

        if (sys_get('dp_sync_noemail')) {
            $donor_data['no_email'] = $member->email_opt_in ? 'N' : 'Y';
        }

        if (sys_get('dp_sync_salutation')) {
            $donor_data['salutation'] = $member->salutation;
        }

        // find the donor
        $donor = $this->findOrNewDonor($donor_data);

        // failed
        if (! $donor) {
            return false;
        }

        // update member with new donor_id
        $member->donor_id = (int) $donor->donor_id;
        $member->save();

        // return the donor
        return $donor;
    }

    /**
     * Update an account's info based on the donors info
     *
     * @param \Ds\Models\Member $member
     * @return \Ds\Models\Member
     */
    public function updateAccountFromDonor(Member $member)
    {
        // grab the donor
        $donor = $this->donor($member->donor_id);

        if (! $donor) {
            throw new MessageException('Cannot update supporter from donor. Donor id ' . $member->donor_id . ' does not exist.');
        }

        // update member
        $member->bill_address_01 = $donor->address;
        $member->bill_address_02 = $donor->address2;
        $member->bill_city = $donor->city;
        $member->bill_state = $donor->state;
        $member->bill_zip = $donor->zip;
        $member->bill_country = $donor->country;
        $member->bill_email = $donor->email;

        if (! $member->sms_verified) {
            $member->bill_phone = $donor->home_phone;
        }

        // organization
        if ($donor->org_rec == 'Y') {
            $member->bill_organization_name = $donor->last_name;

        // individual
        } else {
            $member->title = $donor->title;
            $member->first_name = $donor->first_name;
            $member->last_name = $donor->last_name;
            $member->bill_title = $donor->title;
            $member->bill_first_name = $donor->first_name;
            $member->bill_last_name = $donor->last_name;
        }

        // account type
        $account_type = AccountType::whereDpCode($donor->donor_type)->first();
        if ($account_type) {
            $member->account_type_id = $account_type->id;
        }

        // save
        $member->save();

        if (sys_get('dp_sync_noemail')) {
            app(MemberService::class)
                ->setMember($member)
                ->updateOptin(
                    $donor->no_email === 'N',
                    'Opted out from syncing with Donor Perfect',
                    MemberOptinSource::DONOR_PERFECT
                );
        }

        // return the member
        return $member;
    }

    /**
     * Update an donor's info based on the account's info
     *
     * @param \Ds\Models\Member $member
     * @return object
     */
    public function updateDonorFromAccount(Member $member)
    {
        if (! isset($member) || ! $member instanceof Member) {
            throw new MessageException('Argument must be a Member.');
        }

        // grab the donor
        $donor = $this->donor($member->donor_id);

        if (! $donor) {
            throw new MessageException('Cannot update donor from supporter. Donor id ' . $member->donor_id . ' does not exist.');
        }

        // update member
        $update_donor = [];
        $update_donor['address'] = $member->bill_address_01;
        $update_donor['address2'] = $member->bill_address_02;
        $update_donor['city'] = $member->bill_city;
        $update_donor['state'] = $member->bill_state;
        $update_donor['zip'] = $member->bill_zip;
        $update_donor['country'] = $member->bill_country;
        $update_donor['email'] = $member->email ?? $member->bill_email;

        $update_donor[sys_get('dp_phone_mapping')] = $member->bill_phone;

        if (sys_get('dp_sync_noemail')) {
            $update_donor['no_email'] = $member->email_opt_in ? 'N' : 'Y';
        }

        if (sys_get('dp_sync_salutation')) {
            $update_donor['salutation'] = $member->salutation;
        }

        // organization
        if ($member->accountType->is_organization ?? false) {
            $update_donor['last_name'] = $member->bill_organization_name;
            $update_donor['first_name'] = trim($member->first_name . ' ' . $member->last_name);

        // individual
        } else {
            $update_donor['title'] = $member->title;
            $update_donor['first_name'] = $member->first_name;
            $update_donor['last_name'] = $member->last_name;
        }

        // account type
        $account_type = AccountType::whereDpCode($donor->donor_type)->first();
        if ($member->accountType->dp_code ?? false) {
            $update_donor['donor_type'] = $member->accountType->dp_code;
        }

        // save
        $this->updateDonor($member->donor_id, $update_donor);

        // return the donor
        return $this->donor($member->donor_id);
    }

    /**
     * Create a member in GiveCLoud from a Donor ID
     *
     * @param int $donor_id
     * @return \Ds\Models\Member
     */
    public function createAccountFromDonorId($donor_id)
    {
        // create the member
        $member = new Member;
        $member->donor_id = $donor_id;

        // return the member we created
        return $this->updateAccountFromDonor($member);
    }

    /**
     * Push an order to DonorPerfect
     *
     * This will only run correctly if the order has been
     * fully processed by Givecloud including:
     *
     *  - RPP's have been setup
     *  - Tributes have been setup
     *  - Tax receipts have been issued
     *
     * @param \Ds\Models\Order $order
     * @param int $force_donor_id
     * @return array|bool
     */
    public function pushOrder(Order $order, $force_donor_id = null)
    {
        // bail if dp is not connected
        if (! dpo_is_connected()) {
            throw new MessageException('DonorPerfect is not connected.');
        }

        // reset order
        $order->alt_contact_id = null;
        $order->alt_transaction_id = null;
        $order->save();

        // pre-load all relationships on the order (if not already loaded)
        $order->load('taxReceipts', 'items.tribute', 'items.metadataRelation', 'items.variant.product.metadataRelation', 'items.variant.metadataRelation', 'items.sponsorship', 'items.recurringPaymentProfile', 'items.taxes');

        // If no items are syncable, fail gracefully.
        if (! $this->syncableItemsService->hasSyncableItems($order)) {
            $order->dp_sync_order = false;

            return $order->save();
        }

        // if there is a forced donor id, use it
        if (isset($force_donor_id) && is_numeric($force_donor_id)) {
            $donor = $this->donor($force_donor_id);

            if (! $donor) {
                throw new MessageException('Donor id (' . $force_donor_id . ') does not exist.');
            }

            // if the order has a member, use it's donor_id
        // or use the member's data to create a donor_id
        } elseif ($order->member) {
            // verify donor
            $order->member->verifyDpo();

            // get the donor id
            $donor = $this->donor($order->member->donor_id);

        // otherwise, find or create a donor using the
        // billing info supplied on the order
        } else {
            // find or create a donor
            $donor = $this->findOrNewDonor([
                'title' => $order->billing_title,
                'first_name' => $order->billing_first_name,
                'last_name' => $order->billing_last_name,
                'email' => $order->billingemail,
                'address' => $order->billingaddress1,
                'city' => $order->billingcity,
                'state' => $order->billingstate,
                'zip' => $order->billingzip,
                'country' => $order->billingcountry,
                'phone' => $order->billingphone,
                'organization' => ($order->accountType && $order->accountType->is_organization) ? $order->billing_organization_name : null,
                'donor_type' => ($order->accountType && $order->accountType->dp_code) ? $order->accountType->dp_code : null,
                'no_email' => $order->email_opt_in ? 'N' : 'Y',
                // 'udfs'         => $this->metaUdfsForDonor($order)
            ]);
        }

        // no donor created? bail
        if (! $donor) {
            throw new MessageException('No donor to use.');
        }

        // update the order with our progress so far
        $order->alt_contact_id = (int) $donor->donor_id;
        $order->save();

        if ($this->shouldUpdateDonorMembership($order)) {
            $this->updateDonorMembership($donor->donor_id, $order->member->membershipTimespan->pivot);
        }

        $master_gift = [
            'gc_invoice_number' => $order->invoicenumber,
            'donor_id' => (int) $donor->donor_id,
            'record_type' => 'M',
            'check_ref_number' => $order->check_number ?: $order->payment_other_reference ?: $order->confirmationnumber,
            'date' => $order->createddatetime,
            'amount' => money($order->totalamount, $order->currency_code)->getAmount(),
            'currency' => $order->currency_code,
            'gl' => 'SEE_SPLIT',
            'solicit_code' => 'SEE_SPLIT',
            'sub_solicit_code' => 'SEE_SPLIT',
            'campaign' => 'SEE_SPLIT',
            'gift_type' => 'SEE_SPLIT',
            'ty_letter_no' => 'SEE_SPLIT',
            'split_gifts' => [],
            'pledges' => [],
            'udfs' => $this->metaUdfsForOrder($order),

            // SafeSave transaction_id only
            'transaction_id' => data_get($order, 'paymentProvider.provider') === 'safesave' ? $order->confirmationnumber : null,

            // Reference
            'gc_reference' => $this->getReferenceCoding($order),
        ];

        // tax receipt
        if (sys_get('tax_receipt_pdfs') == 1 && $order->taxReceipt) {
            $receipt_data = [
                'rcpt_type' => 'I',
                'rcpt_num' => $order->taxReceipt->number,
                'rcpt_date' => $order->taxReceipt->issued_at->format('m/d/Y'),
                'rcpt_amount' => $order->taxReceipt->amount,
            ];
        } else {
            $receipt_data = null;
        }

        $payment_method = $this->pushPaymentMethodFromOrder($donor->donor_id, $order);

        // commit to dp
        if ($this->shouldEnableSplitGiftsForOrder($order)) {
            $master_gift = $this->newGift($master_gift);
            $order->alt_transaction_id = $master_gift['gift_id'];
            $order->save();

            app(ExternalReferencesService::class)->upsert($order, $master_gift['gift_id']);
        }

        // loop over each item in the order and create split gifts
        $order->items->each(function ($item) use ($donor, &$master_gift, $order, $receipt_data, $payment_method) {
            if (! $this->syncableItemsService->itemIsSyncable($item)) {
                return;
            }

            $item_amount = sys_get('dp_dcc_is_separate_gift')
                ? $item->total
                : $item->total + $item->dcc_amount;

            // base data
            $split_gift = [
                'gc_invoice_number' => $order->invoicenumber,
                'record_type' => 'G',
                'donor_id' => (int) $donor->donor_id,
                'check_ref_number' => $order->check_number ?: $order->confirmationnumber,
                'date' => toLocalFormat($order->ordered_at ?? $order->createddatetime, 'm/d/Y'),
                'amount' => money($item_amount, $order->currency_code)->getAmount(),
                'currency' => $order->currency_code,
                'gl' => $item->gl_code,
                'campaign' => $item->sponsorship->meta2 ?? $item->variant->metadata->dp_campaign ?? $item->variant->product->meta2 ?? null,
                'solicit_code' => $item->sponsorship->meta3 ?? $item->variant->metadata->dp_solicit ?? $item->variant->product->meta3 ?? null,
                'sub_solicit_code' => $item->sponsorship->meta4 ?? $item->variant->metadata->dp_subsolicit ?? $item->variant->product->meta4 ?? null,
                'gift_type' => $item->sponsorship->meta5 ?? $item->variant->metadata->dp_gift_type ?? $item->variant->product->meta5 ?? null,
                'fmv' => ($item->sponsorship->meta6 ?? $item->variant->metadata->dp_fair_market_value ?? $item->variant->product->meta6 ?? false) ? $item->total : null,
                'ty_letter_no' => $item->sponsorship->meta7 ?? $item->variant->metadata->dp_ty_letter_no ?? $item->variant->product->meta7 ?? null,
                'gift_narrative' => $item->sponsorship->meta8 ?? $item->variant->metadata->dp_gift_narrative ?? $item->variant->product->meta8 ?? null,
                'gift_narrative_2' => sys_get('dp_order_comments_to_narrative') ? $order->comments : null,
                'acknowledgepref' => $item->sponsorship->meta23 ?? $item->variant->metadata->dp_acknowledgepref ?? $item->variant->product->meta23 ?? null,
                'no_calc' => $item->variant->metadata->dp_no_calc ?? $item->variant->product->dpo_nocalc ?? null,
                'udfs' => $this->metaUdfsForOrder($order),
                'tribute' => null,
                'soft_credits' => [],

                // SafeSave transaction_id only
                'transaction_id' => data_get($order, 'paymentProvider.provider') === 'safesave' ? $order->confirmationnumber : null,

                // Reference
                'gc_reference' => $this->getReferenceCoding($item),
            ];

            if (sys_get('dp_enable_ty_date')) {
                $orderReceivedEmail = Email::where('type', 'customer_order_received')->first();
                if ($orderReceivedEmail && $orderReceivedEmail->is_active && Swift_Validate::email($order->billingemail)) {
                    $split_gift['emailsentty_date'] = toLocalFormat($order->confirmationdatetime, 'm/d/Y');
                    $split_gift['ty_date'] = toLocalFormat($order->confirmationdatetime, 'm/d/Y');
                }
            }

            // if split gift
            if ($this->shouldEnableSplitGiftsForOrder($order)) {
                $split_gift['split_gift'] = 'Y';
                $split_gift['glink'] = (int) $master_gift['gift_id'];
            }

            // add receipt data
            if (isset($receipt_data) && $item->is_receiptable) {
                $split_gift = array_merge($split_gift, $receipt_data);
            }

            // add item udf data
            $split_gift['udfs'] = array_merge($split_gift['udfs'], $this->metaUdfsForItem($item));

            // order item metadata overrides
            if (! empty($item->metadata())) {
                $this->applyItemMetadataOverrides($split_gift, $item);
            }

            // is there a fundraising page to soft credit?
            if (sys_get('dp_p2p_soft_credits') && $item->fundraisingPage) {
                $fundraisingPageAccounts = [
                    $item->fundraisingPageAccount ?? null,
                    $item->fundraisingPage->memberOrganizer ?? null,
                ];

                foreach ($fundraisingPageAccounts as $account) {
                    if (empty($account) || ($order->member && $order->member->id === $account->id)) {
                        continue;
                    }

                    $account->verifyDpo();

                    // keying on donor id prevents a donor from receiving multiple soft credits for a donation
                    // if they are both the organizer and the account linked to fundraising page donation
                    $split_gift['soft_credits'][$account->donor_id] = [
                        'donor_id' => $account->donor_id,
                    ];
                }
            }

            // is there a tribute?
            // if so, push the tribute
            if ($item->tribute) {
                $split_gift['tribute'] = [
                    'code' => $item->tribute->tributeType->dp_id,
                    'name' => $item->tribute->name,
                    'message' => $item->tribute->message,
                    'notify_donor' => null,
                ];

                // if there's a recipient
                if ($item->tribute->notify) {
                    $split_gift['tribute']['notify_donor'] = [
                        'name' => $item->tribute->notify_name,
                        'email' => $item->tribute->notify_email,
                        'address' => $item->tribute->notify_address,
                        'city' => $item->tribute->notify_city,
                        'state' => $item->tribute->notify_state,
                        'zip' => $item->tribute->notify_zip,
                        'country' => $item->tribute->notify_country,
                    ];
                }
            }

            // if this is a recurring item
            if ($item->is_recurring) {
                // if there is an recurring payment profile, lets use it
                if ($item->recurringPaymentProfile && sys_get('rpp_donorperfect') == 0) {
                    $rpp = $item->recurringPaymentProfile;

                // if there isn't lets create a temp one
                } else {
                    $rpp = new RecurringPaymentProfile;
                    $rpp->amt = $item->recurring_amount;
                    $rpp->billing_period = $item->recurring_frequency;
                    $rpp->profile_start_date = $rpp->getFirstPossibleStartDate(
                        sys_get('rpp_default_type'),
                        $item->recurring_day,
                        $item->recurring_day_of_week,
                        $item->recurring_with_initial_charge ? 'one-time' : null
                    );
                }

                // create a pledge (a recurring promise to give in DP)
                $pledge = $split_gift;

                // get frequency
                switch ($rpp->billing_period) {
                    case 'Day':       $frequency = 'D'; break;
                    case 'Week':      $frequency = 'W'; break;
                    case 'SemiMonth': $frequency = 'BW'; break;
                    case 'Month':     $frequency = 'M'; break;
                    case 'Quarter':   $frequency = 'Q'; break;
                    case 'SemiYear':  $frequency = 'BA'; break;
                    case 'Year':      $frequency = 'A'; break;
                    default:          $frequency = 'M'; break;
                }

                // pledge attributes
                $pledge['bill'] = $rpp->amt;
                $pledge['start_date'] = toLocalFormat($rpp->profile_start_date, 'm/d/Y');
                $pledge['frequency'] = $frequency;

                $pledge['gc_reference'] = $this->getReferenceCoding($item, ExternalReferenceType::PLEDGE);

                // link payment method
                if (sys_get('rpp_donorperfect') == 1) {
                    $pledge['vault_id'] = $payment_method['vault_id'] ?? null;
                    $pledge['udfs']['eft'] = 'Y';
                } else {
                    $pledge['udfs']['eft'] = 'N';
                }

                // ditch the rpp reference
                unset($rpp);

                // push to dp
                $pledge = $this->newPledge($pledge);

                app(ExternalReferencesService::class)->upsert($item, $pledge['gift_id'], ExternalReferenceType::PLEDGE);

                // add to log
                $master_gift['pledges'][] = $pledge;

                // update the split gift to represent a payment
                // against the pledge we created above
                // (even if its $0)
                $split_gift['plink'] = $pledge['gift_id'];
                $split_gift['pledge_payment'] = 'Y';
            }

            // push to dp
            $split_gift = $this->newGift($split_gift);

            // save to OrderItem
            $item->alt_transaction_id = (int) $split_gift['gift_id'];
            $item->save();

            app(ExternalReferencesService::class)->upsert($item, $split_gift['gift_id'], ExternalReferenceType::ITEM);

            // save to Order
            $order->alt_transaction_id = $this->appendToCsvStr($order->alt_transaction_id, $split_gift['gift_id']);
            $order->save();

            // add to log
            $master_gift['split_gifts'][] = $split_gift;
        });

        // push shipping
        if ($order->shipping_amount > 0 && $this->syncableItemsService->orderHasSyncableShippableItems($order)) {
            // shipping gift data
            $shipping_gift_data = [
                'gc_invoice_number' => $order->invoicenumber,
                'record_type' => 'G',
                'donor_id' => (int) $donor->donor_id,
                'check_ref_number' => $order->check_number ?: $order->confirmationnumber,
                'date' => toLocalFormat($order->ordered_at ?? $order->createddatetime, 'm/d/Y'),
                'amount' => money($order->shipping_amount, $order->currency_code)->getAmount(),
                'currency' => $order->currency_code,
                'gl' => sys_get('dp_shipping_gl'),
                'solicit_code' => sys_get('dp_shipping_solicit'),
                'sub_solicit_code' => sys_get('dp_shipping_subsolicit'),
                'campaign' => sys_get('dp_shipping_campaign'),
                'gift_type' => sys_get('dp_shipping_gift_type'),
                'fmv' => sys_get('dp_shipping_fair_mkt_val') ? $order->shipping_amount : null,
                'ty_letter_no' => sys_get('dp_shipping_ty_letter_code'),
                'gift_narrative' => sys_get('dp_shipping_gift_memo'),
                'no_calc' => sys_get('dp_shipping_no_calc'),
                'acknowledgepref' => sys_get('dp_shipping_acknowledgepref'),
                'udfs' => $this->metaUdfsForOrder($order),

                // SafeSave transaction_id only
                'transaction_id' => data_get($order, 'paymentProvider.provider') === 'safesave' ? $order->confirmationnumber : null,

                // Reference
                'gc_reference' => $this->getReferenceCoding($order, ExternalReferenceType::SHIPPING),
            ];

            // if split gift
            if ($this->shouldEnableSplitGiftsForOrder($order)) {
                $shipping_gift_data['split_gift'] = 'Y';
                $shipping_gift_data['glink'] = (int) $master_gift['gift_id'];
            }

            // add item udf data
            $shipping_gift_data['udfs'] = array_merge($shipping_gift_data['udfs'], $this->metaUdfsForItem('Shipping'));

            // shipping gift custom fields
            foreach ($this->_customFields() as $field) {
                if (trim(sys_get('dp_shipping_' . $field . '_value')) !== '') {
                    $shipping_gift_data['udfs'][strtolower(sys_get('dp_' . $field . '_field'))] = trim(sys_get('dp_shipping_' . $field . '_value'));
                }
            }

            $shipping_gift_data = $this->newGift($shipping_gift_data);
            $master_gift['split_gifts'][] = $shipping_gift_data;

            // track the shipping gift on the order itself
            $order->alt_transaction_id = $this->appendToCsvStr($order->alt_transaction_id, $shipping_gift_data['gift_id']);
            $order->save();

            app(ExternalReferencesService::class)->upsert($order, $shipping_gift_data['gift_id'], ExternalReferenceType::SHIPPING);
        }

        // push dcc
        if ($order->dcc_total_amount > 0
            && sys_get('dp_dcc_is_separate_gift') == 1
            && $this->syncableItemsService->orderHasSyncableItemsWithDcc($order)) {
            // dcc gift data
            $dcc_gift_data = [
                'gc_invoice_number' => $order->invoicenumber,
                'record_type' => 'G',
                'donor_id' => (int) $donor->donor_id,
                'check_ref_number' => $order->check_number ?: $order->confirmationnumber,
                'date' => toLocalFormat($order->ordered_at ?? $order->createddatetime, 'm/d/Y'),
                'amount' => money($order->dcc_total_amount, $order->currency_code)->getAmount(),
                'currency' => $order->currency_code,
                'gl' => sys_get('dp_dcc_gl'),
                'solicit_code' => sys_get('dp_dcc_solicit'),
                'sub_solicit_code' => sys_get('dp_dcc_subsolicit'),
                'campaign' => sys_get('dp_dcc_campaign'),
                'gift_type' => sys_get('dp_dcc_gift_type'),
                'fmv' => sys_get('dp_dcc_fair_mkt_val') ? money($order->dcc_total_amount, $order->currency_code)->getAmount() : null,
                'ty_letter_no' => sys_get('dp_dcc_ty_letter_code'),
                'gift_narrative' => sys_get('dp_dcc_gift_memo'),
                'no_calc' => sys_get('dp_dcc_no_calc'),
                'acknowledgepref' => sys_get('dp_dcc_acknowledgepref'),
                'udfs' => $this->metaUdfsForOrder($order),

                // SafeSave transaction_id only
                'transaction_id' => data_get($order, 'paymentProvider.provider') === 'safesave' ? $order->confirmationnumber : null,

                // Reference
                'gc_reference' => $this->getReferenceCoding($order, ExternalReferenceType::DCC),
            ];

            // if split gift
            if ($this->shouldEnableSplitGiftsForOrder($order)) {
                $dcc_gift_data['split_gift'] = 'Y';
                $dcc_gift_data['glink'] = (int) $master_gift['gift_id'];
            }

            // add item udf data
            $dcc_gift_data['udfs'] = array_merge($dcc_gift_data['udfs'], $this->metaUdfsForItem('Dcc'));

            //  dcc gift custom fields
            foreach ($this->_customFields() as $field) {
                if (trim(sys_get('dp_dcc_' . $field . '_value')) !== '') {
                    $dcc_gift_data['udfs'][strtolower(sys_get('dp_' . $field . '_field'))] = trim(sys_get('dp_dcc_' . $field . '_value'));
                }
            }

            $dcc_gift_data = $this->newGift($dcc_gift_data);
            $master_gift['split_gifts'][] = $dcc_gift_data;

            // track the dcc gift on the order itself
            $order->alt_transaction_id = $this->appendToCsvStr($order->alt_transaction_id, $dcc_gift_data['gift_id']);
            $order->save();

            app(ExternalReferencesService::class)->upsert($order, $dcc_gift_data['gift_id'], ExternalReferenceType::DCC);
        }

        // push tax
        if ($order->taxtotal > 0 && $this->syncableItemsService->orderHasSyncableTaxableItems($order)) {
            // tax gift data
            $tax_gift_data = [
                'gc_invoice_number' => $order->invoicenumber,
                'record_type' => 'G',
                // 'glink'             => (int) $master_gift['gift_id'],
                // 'split_gift'        => 'Y',
                'donor_id' => (int) $donor->donor_id,
                'check_ref_number' => $order->check_number ?: $order->confirmationnumber,
                'date' => toLocalFormat($order->ordered_at ?? $order->createddatetime, 'm/d/Y'),
                'amount' => money($order->taxtotal, $order->currency_code)->getAmount(),
                'currency' => $order->currency_code,
                'gl' => sys_get('dp_tax_gl'),
                'solicit_code' => sys_get('dp_tax_solicit'),
                'sub_solicit_code' => sys_get('dp_tax_subsolicit'),
                'campaign' => sys_get('dp_tax_campaign'),
                'gift_type' => sys_get('dp_tax_gift_type'),
                'fmv' => (sys_get('dp_tax_fair_mkt_val')) ? money($order->taxtotal, $order->currency_code)->getAmount() : null,
                'ty_letter_no' => sys_get('dp_tax_ty_letter_code'),
                'gift_narrative' => sys_get('dp_tax_gift_memo'),
                'no_calc' => sys_get('dp_tax_no_calc'),
                'acknowledgepref' => sys_get('dp_tax_acknowledgepref'),
                'udfs' => $this->metaUdfsForOrder($order),

                // SafeSave transaction_id only
                'transaction_id' => data_get($order, 'paymentProvider.provider') === 'safesave' ? $order->confirmationnumber : null,

                // Reference
                'gc_reference' => $this->getReferenceCoding($order, ExternalReferenceType::TAX),
            ];

            // if split gift
            if ($this->shouldEnableSplitGiftsForOrder($order)) {
                $tax_gift_data['split_gift'] = 'Y';
                $tax_gift_data['glink'] = (int) $master_gift['gift_id'];
            }

            // add item udf data
            $tax_gift_data['udfs'] = array_merge($tax_gift_data['udfs'], $this->metaUdfsForItem('Taxes'));

            // tax gift custom fields
            foreach ($this->_customFields() as $field) {
                if (trim(sys_get('dp_tax_' . $field . '_value')) !== '') {
                    $tax_gift_data['udfs'][strtolower(sys_get('dp_' . $field . '_field'))] = trim(sys_get('dp_tax_' . $field . '_value'));
                }
            }

            $tax_gift_data = $this->newGift($tax_gift_data);
            $master_gift['split_gifts'][] = $tax_gift_data;

            // track the tax gift on the order itself
            $order->alt_transaction_id = $this->appendToCsvStr($order->alt_transaction_id, $tax_gift_data['gift_id']);
            $order->save();

            app(ExternalReferencesService::class)->upsert($order, $tax_gift_data['gift_id'], ExternalReferenceType::TAX);
        }

        // log what we're pushing to dp
        if (sys_get('dp_logging')) {
            app('log')->channel('donorperfect')->info('Pushed contribution to DP', [$master_gift]);
        }

        // return the finalized data
        return $master_gift;
    }

    public function shouldUpdateDonorMembership(Order $order): bool
    {
        if (empty($order->member->membershipTimespan->dp_id)) {
            return false;
        }

        foreach ($order->items as $item) {
            if (empty($item->variant->membership_id)) {
                continue;
            }

            if ($item->variant->membership_id === $order->member->membershipTimespan->id) {
                return true;
            }
        }

        return false;
    }

    public function shouldEnableSplitGiftsForOrder(Order $order): bool
    {
        if (! sys_get('dp_enable_split_gifts')) {
            return false;
        }

        if ($order->items->count() > 1) {
            return true;
        }

        if ($order->shipping_amount > 0) {
            return true;
        }

        if ($order->taxtotal > 0) {
            return true;
        }

        if ($order->dcc_total_amount > 0 && sys_get('dp_dcc_is_separate_gift')) {
            return true;
        }

        return false;
    }

    /**
     * Build meta data from order for UDFs.
     *
     * @param \Ds\Models\Order $order
     * @return array
     */
    public function metaUdfsForOrder(Order $order)
    {
        $meta = [];

        // meta data - payment_type (Visa / American Express / MasterCard / PayPal / GoCardless)
        if (sys_get('dp_meta_payment_method')) {
            if ($order->totalamount > 0) {
                $meta[strtolower(sys_get('dp_meta_payment_method'))] = ($order->billingcardtype ?? $order->payment_type);
            } else {
                $meta[strtolower(sys_get('dp_meta_payment_method'))] = sys_get('dp_meta_payment_method_default');
            }
        }

        // meta data - is_rpp
        if (sys_get('dp_meta_is_rpp')) {
            $meta[strtolower(sys_get('dp_meta_is_rpp'))] = 'N';
        }

        // add order number
        if (sys_get('dp_meta_order_number')) {
            $meta[strtolower(sys_get('dp_meta_order_number'))] = $order->invoicenumber;
        }

        // add order source
        if (sys_get('dp_meta_order_source')) {
            $meta[strtolower(sys_get('dp_meta_order_source'))] = $order->source;
        }

        // add special notes
        if (sys_get('dp_meta_special_notes')) {
            $meta[strtolower(sys_get('dp_meta_special_notes'))] = $order->comments;
        }

        // add referral source
        if (sys_get('dp_meta_referral_source')) {
            $meta[strtolower(sys_get('dp_meta_referral_source'))] = $order->referral_source;
        }

        // account name
        if (sys_get('dp_meta_donor_name') && isset($order->member)) {
            $meta[strtolower(sys_get('dp_meta_donor_name'))] = $order->member->display_name;
        }

        // tracking source
        if (sys_get('dp_meta_tracking_source') && isset($order->tracking_source)) {
            $meta[strtolower(sys_get('dp_meta_tracking_source'))] = $order->tracking_source;
        }

        // tracking medium
        if (sys_get('dp_meta_tracking_medium') && isset($order->tracking_medium)) {
            $meta[strtolower(sys_get('dp_meta_tracking_medium'))] = $order->tracking_medium;
        }

        // tracking campaign
        if (sys_get('dp_meta_tracking_campaign') && isset($order->tracking_campaign)) {
            $meta[strtolower(sys_get('dp_meta_tracking_campaign'))] = $order->tracking_campaign;
        }

        // tracking term
        if (sys_get('dp_meta_tracking_term') && isset($order->tracking_term)) {
            $meta[strtolower(sys_get('dp_meta_tracking_term'))] = $order->tracking_term;
        }

        // tracking content
        if (sys_get('dp_meta_tracking_content') && isset($order->tracking_content)) {
            $meta[strtolower(sys_get('dp_meta_tracking_content'))] = $order->tracking_content;
        }

        return $meta;
    }

    /**
     * Build meta data from order for UDFs.
     *
     * @param \Ds\Models\OrderItem|string $item
     * @return array
     */
    public function metaUdfsForItem($item)
    {
        $meta = [];

        if ($item === 'Taxes' || $item === 'Shipping' || $item === 'Dcc') {
            // add qty udf
            if (sys_get('dp_meta_item_qty')) {
                $meta[strtolower(sys_get('dp_meta_item_qty'))] = 1;
            }

            // add product description
            if (sys_get('dp_meta_item_description')) {
                $meta[strtolower(sys_get('dp_meta_item_description'))] = $item;
            }
        } elseif (is_a($item, 'Ds\Models\OrderItem')) {
            // add qty udf
            if (sys_get('dp_meta_item_qty')) {
                $meta[strtolower(sys_get('dp_meta_item_qty'))] = $item->qty;
            }

            // add product description
            if (sys_get('dp_meta_item_description')) {
                $meta[strtolower(sys_get('dp_meta_item_description'))] = $item->description . ' (' . ($item->reference) . ')';
            }

            if ($item->sponsorship) {
                // add sponsorship reference number
                if (sys_get('dp_meta_item_code')) {
                    $meta[strtolower(sys_get('dp_meta_item_code'))] = $item->sponsorship->reference_number;
                }
            }

            if ($item->variant && $item->variant->product) {
                // add product name
                if (sys_get('dp_meta_item_name')) {
                    $meta[strtolower(sys_get('dp_meta_item_name'))] = $item->variant->product->name;
                }

                // add variant name
                if (sys_get('dp_meta_item_variant_name')) {
                    $meta[strtolower(sys_get('dp_meta_item_variant_name'))] = $item->variant->variantname;
                }

                // add product code
                if (sys_get('dp_meta_item_code')) {
                    $meta[strtolower(sys_get('dp_meta_item_code'))] = $item->code;
                }

                // add fair market value
                if (sys_get('dp_meta_item_fmv')) {
                    $meta[strtolower(sys_get('dp_meta_item_fmv'))] = $item->variant->fair_market_value * $item->qty;
                }
            }

            // custom fields
            foreach ($this->_customFields() as $udf_field) {
                // find all custom fields that are to be mapped over
                $custom_field_values = $item->fields->filter(
                    function ($field) use ($udf_field) {
                        return $field->map_to_product_meta == $udf_field;
                    }
                );

                // product/sponsorship value
                if ($item->variant) {
                    $item_val = $item->variant->metadata[$udf_field] ?: $item->variant->product->{$udf_field} ?? '';
                } elseif ($item->sponsorship) {
                    $item_val = $item->sponsorship->{$udf_field};
                } else {
                    $item_val = '';
                }

                // udf value
                $udf_value = trim((($custom_field_values) ? $custom_field_values->implode('value') : '') . ' ' . $item_val);

                // add to custom fields
                if ($udf_value) {
                    $meta[strtolower(sys_get('dp_' . $udf_field . '_field'))] = $udf_value;
                }
            }

            // if there is a tribute
            if (isset($item->tribute)) {
                // tribute name
                if (sys_get('dp_meta_tribute_name')) {
                    $meta[strtolower(sys_get('dp_meta_tribute_name'))] = $item->tribute->name;
                }

                // tribute type
                if (sys_get('dp_meta_tribute_type') && isset($item->tribute->tributeType)) {
                    $meta[strtolower(sys_get('dp_meta_tribute_type'))] = $item->tribute->tributeType->label;
                }

                // tribute notify name
                if (sys_get('dp_meta_tribute_notify_name')) {
                    $meta[strtolower(sys_get('dp_meta_tribute_notify_name'))] = $item->tribute->notify_name;
                }

                // tribute notify email
                if (sys_get('dp_meta_tribute_notify_email')) {
                    $meta[strtolower(sys_get('dp_meta_tribute_notify_email'))] = $item->tribute->notify_email;
                }

                // tribute notify address
                if (sys_get('dp_meta_tribute_notify_address')) {
                    $meta[strtolower(sys_get('dp_meta_tribute_notify_address'))] = $item->tribute->notify_full_address;
                }

                // tribute notify type (letter / email)
                if (sys_get('dp_meta_tribute_notify_type')) {
                    $meta[strtolower(sys_get('dp_meta_tribute_notify_type'))] = $item->tribute->notify;
                }

                // tribute personal message
                if (sys_get('dp_meta_tribute_personal_message')) {
                    $meta[strtolower(sys_get('dp_meta_tribute_personal_message'))] = $item->tribute->message;
                }
            }

            // peer to peer solicit code
            if ($item->fundraisingPage && sys_get('dp_p2p_url_field')) {
                $meta[strtolower(sys_get('dp_p2p_url_field'))] = $this->verifiedCode(sys_get('dp_p2p_url_field'), Str::limit($item->fundraisingPage->url, 30, ''), true);
            }
        }

        return $meta;
    }

    /**
     * Override values on the gift with metadata on the OrderItem
     *
     * @param array $split_gift
     * @param \Ds\Models\OrderItem $item
     */
    public function applyItemMetadataOverrides(&$split_gift, $item)
    {
        // loop over meta data
        collect($item->metadata())
            ->filter(function ($val, $key) {
                return ! empty($val) && strpos($key, 'dp_') === 0;
            })->each(function ($val, $key) use (&$split_gift, $item) {
                if ($key === 'dp_gl_code') {
                    $key = 'gl';
                } else {
                    $key = str_replace('dp_', '', strtolower($key));
                }
                if ($key === 'fmv' || $key === 'fair_market_value') {
                    $key = 'fmv';
                    $val = $val === 'Y' ? $item->total : null;
                }
                $split_gift[$key] = $val;
            });

        // loop over udf meta data
        collect($item->metadata())
            ->filter(function ($val, $key) {
                return ! empty($val) && strpos($key, 'dpudf_') === 0;
            })->each(function ($val, $key) use (&$split_gift) {
                $split_gift['udfs'][str_replace('dpudf_', '', strtolower($key))] = $val;
            });
    }

    /**
     * Build meta data for donor for UDFs.
     *
     * @param \Ds\Models\Order|\Ds\Models\Member $model
     * @return array
     */
    public function metaUdfsForDonor($model)
    {
        $meta = [];

        // if we're deriving the values from an ORDER,
        // grab the referral_source from the member,
        // fallback to order
        if ($model instanceof Order) {
            // grab referral source from order
            $referral_source = $model->referral_source;

            // try to grab from member if it doesn't exist on order
            if (! $referral_source && $model->member && $model->member->referral_source) {
                $referral_source = $model->member->referral_source;
            }

            // if we're deriving the values from a MEMBER
        } elseif ($model instanceof Member) {
            $referral_source = $model->referral_source;
        }

        // meta data - is_rpp
        if (sys_get('dp_meta_referral_source') && $referral_source) {
            $meta[strtolower(sys_get('dp_meta_referral_source'))] = $referral_source;
        }

        return $meta;
    }

    /**
     * Push a transaction.
     *
     * This will push one gift into DP according to the transaction.
     * If the order item is linked to a PLEDGE in DP, it will add
     * a payment against the pledge.
     *
     * @param \Ds\Models\Transaction $txn
     * @return array|bool
     */
    public function pushTransaction(Transaction $txn)
    {
        // bail if dp is not connected
        if (! dpo_is_connected()) {
            throw new MessageException('DonorPerfect is not connected.');
        }

        // If item is not syncable, fail gracefully.
        if (! $this->syncableItemsService->itemIsSyncable($txn->recurringPaymentProfile->order_item)) {
            $txn->dp_auto_sync = false;

            return $txn->save();
        }

        // get the gift/pledge of the order item
        if (! empty($txn->recurringPaymentProfile->dp_pledge_id_override)) {
            $pledge_id = $txn->recurringPaymentProfile->dp_pledge_id_override;
        } elseif (isset($txn->recurringPaymentProfile->order_item) && $txn->recurringPaymentProfile->order_item->alt_transaction_id) {
            $order_item_gift = $this->gift($txn->recurringPaymentProfile->order_item->alt_transaction_id);
            if ($order_item_gift && $order_item_gift->pledge) {
                $pledge_id = $order_item_gift->pledge->gift_id;
            } elseif ($order_item_gift && $order_item_gift->record_type == 'P') {
                $pledge_id = $order_item_gift->gift_id;
            }
        }

        // donor id
        $txn->recurringPaymentProfile->member->verifyDpo();

        // gc invoice number should include an rpp reference
        $gc_invoice_number = [];
        if ($txn->recurringPaymentProfile->order) {
            $gc_invoice_number[] = $txn->recurringPaymentProfile->order->invoicenumber;
        }
        $gc_invoice_number[] = 'RPP# ' . $txn->recurringPaymentProfile->profile_id;
        $gc_invoice_number = implode(' ', $gc_invoice_number);

        $master_gift = [
            'gc_invoice_number' => $gc_invoice_number,
            'donor_id' => $txn->recurringPaymentProfile->member->donor_id,
            'record_type' => 'M',
            'check_ref_number' => $txn->transaction_id,
            'date' => toLocalFormat($txn->order_time, 'm/d/Y'),
            'amount' => money($txn->amt, $txn->currency_code)->getAmount(),
            'currency' => $txn->currency_code,
            'gl' => 'SEE_SPLIT',
            'solicit_code' => 'SEE_SPLIT',
            'sub_solicit_code' => 'SEE_SPLIT',
            'campaign' => 'SEE_SPLIT',
            'gift_type' => 'SEE_SPLIT',
            'ty_letter_no' => 'SEE_SPLIT',
            'split_gifts' => [],
            'pledges' => [],
            'udfs' => $this->metaUdfsForTransaction($txn),

            'transaction_id' => data_get($txn->paymentMethod, 'paymentProvider.provider') === 'safesave' ? $txn->transaction_id : null,

            // Reference
            'gc_reference' => $this->getReferenceCoding($txn),
        ];

        // commit to dp
        if ($this->shouldEnableSplitGiftsForTransaction($txn)) {
            $master_gift = $this->newGift($master_gift);
            $txn->alt_transaction_id = $master_gift['gift_id'];
            $txn->save();

            app(ExternalReferencesService::class)->upsert($txn, $master_gift['gift_id'], ExternalReferenceType::TXN);
        }

        $amount = $txn->amt - $txn->shipping_amt - $txn->tax_amt;

        if (sys_get('dp_dcc_is_separate_gift')) {
            $amount -= $txn->dcc_amount;
        }

        // push the gift
        $txn_gift = [
            'gc_invoice_number' => $gc_invoice_number,
            'record_type' => 'G',
            'donor_id' => $txn->recurringPaymentProfile->member->donor_id,
            'check_ref_number' => $txn->transaction_id,
            'date' => toLocalFormat($txn->order_time, 'm/d/Y'),
            'amount' => money($amount, $txn->currency_code)->getAmount(),
            'currency' => $txn->currency_code,
            'gl' => $txn->recurringPaymentProfile->gl_code,
            'campaign' => $txn->recurringPaymentProfile->sponsorship->meta2 ?? $txn->recurringPaymentProfile->variant->metadata->dp_campaign ?? $txn->recurringPaymentProfile->product->meta2 ?? null,
            'solicit_code' => $txn->recurringPaymentProfile->sponsorship->meta3 ?? $txn->recurringPaymentProfile->variant->metadata->dp_solicit ?? $txn->recurringPaymentProfile->product->meta3 ?? null,
            'sub_solicit_code' => $txn->recurringPaymentProfile->sponsorship->meta4 ?? $txn->recurringPaymentProfile->variant->metadata->dp_subsolicit ?? $txn->recurringPaymentProfile->product->meta4 ?? null,
            'gift_type' => $txn->recurringPaymentProfile->sponsorship->meta5 ?? $txn->recurringPaymentProfile->variant->metadata->dp_gift_type ?? $txn->recurringPaymentProfile->product->meta5 ?? null,
            'ty_letter_no' => $txn->recurringPaymentProfile->sponsorship->meta7 ?? $txn->recurringPaymentProfile->variant->metadata->dp_ty_letter_no ?? $txn->recurringPaymentProfile->product->meta7 ?? null,
            'gift_narrative' => $txn->recurringPaymentProfile->sponsorship->meta8 ?? $txn->recurringPaymentProfile->variant->metadata->dp_gift_narrative ?? $txn->recurringPaymentProfile->product->meta8 ?? null,
            'acknowledgepref' => $txn->recurringPaymentProfile->sponsorship->meta23 ?? $txn->recurringPaymentProfile->variant->metadata->dp_acknowledgepref ?? $txn->recurringPaymentProfile->product->meta23 ?? null,
            'no_calc' => $txn->recurringPaymentProfile->variant->metadata->dp_no_calc ?? $txn->recurringPaymentProfile->product->dpo_nocalc ?? null,
            'udfs' => $this->metaUdfsForTransaction($txn),
            'tribute' => null,

            'rcpt_num' => ($txn->taxReceipt) ? $txn->taxReceipt->number : null,
            'rcpt_date' => ($txn->taxReceipt) ? $txn->taxReceipt->issued_at : null,
            'rcpt_amount' => ($txn->taxReceipt) ? money($txn->taxReceipt->amount, $txn->taxReceipt->currency_code)->getAmount() : null,

            'plink' => (isset($pledge_id)) ? $pledge_id : null,
            'pledge_payment' => (isset($pledge_id)) ? 'Y' : 'N',

            // SafeSave transaction_id ONLY
            'transaction_id' => data_get($txn->paymentMethod, 'paymentProvider.provider') === 'safesave' ? $txn->transaction_id : null,

            'gc_reference' => $this->getReferenceCoding($txn),
        ];

        // if split gift
        if ($this->shouldEnableSplitGiftsForTransaction($txn)) {
            $txn_gift['split_gift'] = 'Y';
            $txn_gift['glink'] = (int) $master_gift['gift_id'];
            $txn_gift['gc_reference'] = $this->getReferenceCoding($txn, ExternalReferenceType::TXNSPLIT);
        }

        // order item metadata overrides
        if ($txn->recurringPaymentProfile->order_item) {
            $this->applyItemMetadataOverrides($txn_gift, $txn->recurringPaymentProfile->order_item);
        }

        // save to dp
        $txn_gift = $this->newGift($txn_gift);

        // update txn
        $txn->dpo_gift_id = $master_gift['gift_id'] ?? $txn_gift['gift_id'];
        $txn->alt_transaction_id = $this->appendToCsvStr($txn->alt_transaction_id, $txn_gift['gift_id']);
        $txn->save();

        app(ExternalReferencesService::class)->upsert(
            $txn,
            $txn_gift['gift_id'],
            $this->shouldEnableSplitGiftsForTransaction($txn) ? ExternalReferenceType::TXNSPLIT : ExternalReferenceType::TXN
        );

        // add to log
        $master_gift['split_gifts'][] = $txn_gift;

        // push shipping
        if ($txn->shipping_amt > 0) {
            // shipping gift data
            $shipping_gift_data = [
                'gc_invoice_number' => $gc_invoice_number,
                'donor_id' => $txn->recurringPaymentProfile->member->donor_id,
                'record_type' => 'G',
                'check_ref_number' => $txn->transaction_id,
                'date' => toLocalFormat($txn->order_time, 'm/d/Y'),
                'amount' => money($txn->shipping_amt, $txn->currency_code)->getAmount(),
                'currency' => $txn->currency_code,
                'gl' => sys_get('dp_shipping_gl'),
                'solicit_code' => sys_get('dp_shipping_solicit'),
                'sub_solicit_code' => sys_get('dp_shipping_subsolicit'),
                'campaign' => sys_get('dp_shipping_campaign'),
                'gift_type' => sys_get('dp_shipping_gift_type'),
                'fmv' => sys_get('dp_shipping_fair_mkt_val') ? money($txn->shipping_amt, $txn->currency_code)->getAmount() : null,
                'ty_letter_no' => sys_get('dp_shipping_ty_letter_code'),
                'gift_narrative' => sys_get('dp_shipping_gift_memo'),
                'no_calc' => sys_get('dp_shipping_no_calc'),
                'acknowledgepref' => sys_get('dp_shipping_acknowledgepref'),
                'udfs' => $this->metaUdfsForTransaction($txn),

                // SafeSave transaction_id only
                'transaction_id' => data_get($txn->paymentMethod, 'paymentProvider.provider') === 'safesave' ? $txn->transaction_id : null,

                // Reference
                'gc_reference' => $this->getReferenceCoding($txn, ExternalReferenceType::SHIPPING),
            ];

            // if split gift
            if ($this->shouldEnableSplitGiftsForTransaction($txn)) {
                $shipping_gift_data['split_gift'] = 'Y';
                $shipping_gift_data['glink'] = (int) $master_gift['gift_id'];
            }

            // add item udf data
            $shipping_gift_data['udfs'] = array_merge($shipping_gift_data['udfs'], $this->metaUdfsForItem('Shipping'));

            // shipping gift custom fields
            foreach ($this->_customFields() as $field) {
                if (trim(sys_get('dp_shipping_' . $field . '_value')) !== '') {
                    $shipping_gift_data['udfs'][strtolower(sys_get('dp_' . $field . '_field'))] = trim(sys_get('dp_shipping_' . $field . '_value'));
                }
            }

            $shipping_gift_data = $this->newGift($shipping_gift_data);
            $master_gift['split_gifts'][] = $shipping_gift_data;

            // track the shipping gift on the txn itself
            $txn->alt_transaction_id = $this->appendToCsvStr($txn->alt_transaction_id, $shipping_gift_data['gift_id']);
            $txn->save();

            app(ExternalReferencesService::class)->upsert($txn, $shipping_gift_data['gift_id'], ExternalReferenceType::SHIPPING);
        }

        // push dcc
        if ($txn->dcc_amount > 0 && sys_get('dp_dcc_is_separate_gift') == 1) {
            // shipping gift data
            $dcc_gift_data = [
                'gc_invoice_number' => $gc_invoice_number,
                'record_type' => 'G',
                'donor_id' => $txn->recurringPaymentProfile->member->donor_id,
                'check_ref_number' => $txn->transaction_id,
                'date' => toLocalFormat($txn->order_time, 'm/d/Y'),
                'amount' => money($txn->dcc_amount, $txn->currency_code)->getAmount(),
                'currency' => $txn->currency_code,
                'gl' => sys_get('dp_dcc_gl'),
                'solicit_code' => sys_get('dp_dcc_solicit'),
                'sub_solicit_code' => sys_get('dp_dcc_subsolicit'),
                'campaign' => sys_get('dp_dcc_campaign'),
                'gift_type' => sys_get('dp_dcc_gift_type'),
                'fmv' => sys_get('dp_dcc_fair_mkt_val') ? $txn->dcc_amount : null,
                'ty_letter_no' => sys_get('dp_dcc_ty_letter_code'),
                'gift_narrative' => sys_get('dp_dcc_gift_memo'),
                'no_calc' => sys_get('dp_dcc_no_calc'),
                'acknowledgepref' => sys_get('dp_dcc_acknowledgepref'),
                'udfs' => $this->metaUdfsForTransaction($txn),

                // SafeSave transaction_id only
                'transaction_id' => data_get($txn->paymentMethod, 'paymentProvider.provider') === 'safesave' ? $txn->transaction_id : null,

                // Reference
                'gc_reference' => $this->getReferenceCoding($txn, ExternalReferenceType::DCC),
            ];

            // if split gift
            if ($this->shouldEnableSplitGiftsForTransaction($txn)) {
                $dcc_gift_data['split_gift'] = 'Y';
                $dcc_gift_data['glink'] = (int) $master_gift['gift_id'];
            }

            // add item udf data
            $dcc_gift_data['udfs'] = array_merge($dcc_gift_data['udfs'], $this->metaUdfsForItem('Dcc'));

            //  dcc gift custom fields
            foreach ($this->_customFields() as $field) {
                if (trim(sys_get('dp_dcc_' . $field . '_value')) !== '') {
                    $dcc_gift_data['udfs'][strtolower(sys_get('dp_' . $field . '_field'))] = trim(sys_get('dp_dcc_' . $field . '_value'));
                }
            }

            $dcc_gift_data = $this->newGift($dcc_gift_data);
            $master_gift['split_gifts'][] = $dcc_gift_data;

            // track the dcc gift on the order itself
            $txn->alt_transaction_id = $this->appendToCsvStr($txn->alt_transaction_id, $dcc_gift_data['gift_id']);
            $txn->save();

            app(ExternalReferencesService::class)->upsert($txn, $dcc_gift_data['gift_id'], ExternalReferenceType::DCC);
        }

        // push tax
        if ($txn->tax_amt > 0) {
            // tax gift data
            $tax_gift_data = [
                'gc_invoice_number' => $gc_invoice_number,
                'record_type' => 'G',
                'donor_id' => $txn->recurringPaymentProfile->member->donor_id,
                'check_ref_number' => $txn->transaction_id,
                'date' => toLocalFormat($txn->order_time, 'm/d/Y'),
                'amount' => money($txn->tax_amt, $txn->currency_code)->getAmount(),
                'currency' => $txn->currency_code,
                'gl' => sys_get('dp_tax_gl'),
                'solicit_code' => sys_get('dp_tax_solicit'),
                'sub_solicit_code' => sys_get('dp_tax_subsolicit'),
                'campaign' => sys_get('dp_tax_campaign'),
                'gift_type' => sys_get('dp_tax_gift_type'),
                'fmv' => (sys_get('dp_tax_fair_mkt_val')) ? $txn->tax_amt : null,
                'ty_letter_no' => sys_get('dp_tax_ty_letter_code'),
                'gift_narrative' => sys_get('dp_tax_gift_memo'),
                'no_calc' => sys_get('dp_tax_no_calc'),
                'acknowledgepref' => sys_get('dp_tax_acknowledgepref'),
                'udfs' => $this->metaUdfsForTransaction($txn),

                // SafeSave transaction_id only
                'transaction_id' => data_get($txn->paymentMethod, 'paymentProvider.provider') === 'safesave' ? $txn->transaction_id : null,

                // Reference
                'gc_reference' => $this->getReferenceCoding($txn, ExternalReferenceType::TAX),
            ];

            // if split gift
            if ($this->shouldEnableSplitGiftsForTransaction($txn)) {
                $tax_gift_data['split_gift'] = 'Y';
                $tax_gift_data['glink'] = (int) $master_gift['gift_id'];
            }

            // add item udf data
            $tax_gift_data['udfs'] = array_merge($tax_gift_data['udfs'], $this->metaUdfsForItem('Taxes'));

            // tax gift custom fields
            foreach ($this->_customFields() as $field) {
                if (trim(sys_get('dp_tax_' . $field . '_value')) !== '') {
                    $tax_gift_data['udfs'][strtolower(sys_get('dp_' . $field . '_field'))] = trim(sys_get('dp_tax_' . $field . '_value'));
                }
            }

            $tax_gift_data = $this->newGift($tax_gift_data);
            $master_gift['split_gifts'][] = $tax_gift_data;

            // track the tax gift on the order itself
            $txn->alt_transaction_id = $this->appendToCsvStr($txn->alt_transaction_id, $tax_gift_data['gift_id']);
            $txn->save();

            app(ExternalReferencesService::class)->upsert($txn, $tax_gift_data['gift_id'], ExternalReferenceType::TAX);
        }

        // log what we're pushing to dp
        if (sys_get('dp_logging')) {
            app('log')->channel('donorperfect')->info('Pushed txn to DP', [$master_gift]);
        }

        // return saved data
        return $master_gift;
    }

    public function shouldEnableSplitGiftsForTransaction(Transaction $transaction): bool
    {
        if (! sys_get('dp_enable_split_gifts')) {
            return false;
        }

        if ($transaction->shipping_amt > 0) {
            return true;
        }

        if ($transaction->tax_amt > 0) {
            return true;
        }

        if ($transaction->dcc_amount > 0 && sys_get('dp_dcc_is_separate_gift')) {
            return true;
        }

        return false;
    }

    /**
     * Push order refund.
     *
     * This will push one gift into DP according to the
     * order's refund.
     *
     * @param \Ds\Models\Order $order
     * @return bool
     */
    public function pushOrderFullRefund(Order $order)
    {
        // bail if dp is not connected
        if (! dpo_is_connected()) {
            throw new MessageException('DonorPerfect is not connected.');
        }

        // check settings
        if (! sys_get('dp_push_order_refunds')) {
            throw new MessageException('Pushing refunds to DP is disabled. Check your DP integration settings.');
        }

        if ($order->refunded_amt !== $order->totalamount) {
            throw new MessageException('Unable to push full refund to DP. The contribution was not refunded in full.');
        }

        // collect all related gift ids
        $gift_ids = explode(',', $order->alt_transaction_id);
        $gift_ids = array_merge($gift_ids, $order->items->pluck('alt_transaction_id')->toArray());
        $gift_ids = array_unique($gift_ids);
        $gift_ids = array_filter($gift_ids, 'strlen');

        // if no gifts found, error
        if (count($gift_ids) === 0) {
            throw new MessageException('Unable to adjust gifts in DP. The contribution has no gift IDs.');
        }

        // get all the gifts from dp
        $gifts = app('dpo')->table('dpgift')
            ->select('gift_id', 'amount')
            ->whereIn('gift_id', $gift_ids)
            ->get();

        // if no gifts found, error
        if (count($gifts) === 0) {
            throw new MessageException('Unable to adjust gifts in DP. All gifts are missing in DP. (GC:' . implode(',', $gift_ids));
        }

        // if the length doesn't match, throw an error
        if (count($gifts) !== count($gift_ids)) {
            throw new MessageException('Unable to adjust gifts in DP. There are gift ids missing in DonorPerfect. (GC:' . implode(',', $gift_ids) . '; DP:' . $gifts->pluck('gift_id')->implode(',') . ')');
        }

        // loop over each gift and adjust it to 0
        foreach ($gifts as $gift) {
            $this->adjustGift($gift->gift_id, (0 - $gift->amount), toLocalFormat($order->refunded_at, 'm/d/Y'));
        }

        // success
        return true;
    }

    /**
     * Push transaction refund.
     *
     * This will push one gift into DP according to the
     * transaction's refund.
     *
     * @param \Ds\Models\Transaction $txn
     * @return bool
     */
    public function pushTransactionFullRefund(Transaction $txn)
    {
        // bail if dp is not connected
        if (! dpo_is_connected()) {
            throw new MessageException('DonorPerfect is not connected.');
        }

        // check settings
        if (! sys_get('dp_push_txn_refunds')) {
            throw new MessageException('Pushing refunds to DP is disabled. Check your DP integration settings.');
        }

        // if there is no gift id, create that first
        if (! $txn->dpo_gift_id) {
            $this->pushTransaction($txn);
        }

        $gift_ids = collect(explode(',', $txn->alt_transaction_id));

        $gift_ids->each(function ($gift_id) use ($txn) {
            // get the original gift
            $original_gift = $this->gift($gift_id);

            // if no gift error
            if (! $original_gift) {
                throw new MessageException('No original gift in DP.');
            }

            // adjust the gift
            $this->adjustGift($original_gift->gift_id, (0 - $original_gift->amount), toLocalFormat($txn->refunded_at, 'm/d/Y'));
        });

        // success
        return true;
    }

    /**
     * Build meta data from transaction for UDFs.
     *
     * @param \Ds\Models\Transaction $txn
     * @return array
     */
    public function metaUdfsForTransaction(Transaction $txn)
    {
        $meta = [];

        // meta data - payment_type (Visa / American Express / MasterCard / PayPal / GoCardless)
        if (sys_get('dp_meta_payment_method')) {
            $meta[strtolower(sys_get('dp_meta_payment_method'))] = ucwords($txn->paymentMethod->account_type ?? $txn->payment_method_type);
        }

        // meta data - is_rpp
        if (sys_get('dp_meta_is_rpp')) {
            $meta[strtolower(sys_get('dp_meta_is_rpp'))] = 'Y';
        }

        // add order number
        if (sys_get('dp_meta_order_number')) {
            $meta[strtolower(sys_get('dp_meta_order_number'))] = $txn->recurringPaymentProfile->order_item->order->invoicenumber ?? null;
        }

        // add order source
        if (sys_get('dp_meta_order_source')) {
            $meta[strtolower(sys_get('dp_meta_order_source'))] = $txn->recurringPaymentProfile->order_item->order->source ?? null;
        }

        // add referral source
        if (sys_get('dp_meta_referral_source')) {
            $meta[strtolower(sys_get('dp_meta_referral_source'))] = $txn->recurringPaymentProfile->order_item->order->referral_source ?? null;
        }

        // add qty udf
        if (sys_get('dp_meta_item_qty')) {
            $meta[strtolower(sys_get('dp_meta_item_qty'))] = 1;
        }

        // add product description
        if (sys_get('dp_meta_item_description') && isset($txn->recurringPaymentProfile->order_item)) {
            $meta[strtolower(sys_get('dp_meta_item_description'))] = $txn->recurringPaymentProfile->order_item->description . ' (' . $txn->recurringPaymentProfile->order_item->reference . ')';
        }

        if (isset($txn->recurringPaymentProfile->order_item->variant->product, $txn->recurringPaymentProfile->order_item)) {
            // add product name
            if (sys_get('dp_meta_item_name')) {
                $meta[strtolower(sys_get('dp_meta_item_name'))] = $txn->recurringPaymentProfile->order_item->variant->product->name;
            }

            // add variant name
            if (sys_get('dp_meta_item_variant_name')) {
                $meta[strtolower(sys_get('dp_meta_item_variant_name'))] = $txn->recurringPaymentProfile->order_item->variant->variantname;
            }

            // add product code
            if (sys_get('dp_meta_item_code')) {
                $meta[strtolower(sys_get('dp_meta_item_code'))] = $txn->recurringPaymentProfile->order_item->code;
            }

            // add fair market value
            if (sys_get('dp_meta_item_fmv')) {
                $meta[strtolower(sys_get('dp_meta_item_fmv'))] = $txn->recurringPaymentProfile->order_item->variant->fair_market_value * $txn->recurringPaymentProfile->order_item->qty;
            }
        }

        // custom fields
        foreach ($this->_customFields() as $udf_field) {
            // product/sponsorship value
            if (isset($txn->recurringPaymentProfile->order_item->variant->product)) {
                $meta[strtolower(sys_get('dp_' . $udf_field . '_field'))] = trim(
                    $txn->recurringPaymentProfile->order_item->variant->metadata[$udf_field] ?: $txn->recurringPaymentProfile->order_item->variant->product->{$udf_field} ?? ''
                );
            } elseif (isset($txn->recurringPaymentProfile->order_item->sponsorship)) {
                $meta[strtolower(sys_get('dp_' . $udf_field . '_field'))] = trim($txn->recurringPaymentProfile->order_item->sponsorship->{$udf_field});
            }
        }

        return $meta;
    }

    /**
     * Push a PaymentMethod to DonorPerfect from
     * payment data on the order table.
     *
     * Used in DP when a pledge in DP needs to
     * charge a credit card. (Recurring in DP only)
     *
     * @param int $donor_id
     * @param \Ds\Models\Order $order
     * @return array|null
     */
    public function pushPaymentMethodFromOrder(int $donor_id, Order $order)
    {
        if (! sys_get('rpp_donorperfect') || empty($order->vault_id) || $order->recurring_items < 1) {
            return null;
        }

        if (data_get($order, 'paymentProvider.provider') !== 'safesave') {
            return null;
        }

        $data = [
            'donor_id' => $donor_id,
            'vault_id' => $order->vault_id,
            'payment_method' => (($order->billingcardtype === 'Personal Check' || $order->billingcardtype === 'Business Check') ? 'check' : 'creditcard'),
            'created_date' => toLocalFormat($order->createddatetime, 'm/d/Y'),
            'currency' => $order->currency_code,
        ];

        // check specific
        if ($data['payment_method'] === 'check') {
            $data += [
                'account_type' => 'Bank Account',
                'account_last_four' => $order->billingcardlastfour,
                'name_on_account' => $order->billing_name_on_account,
            ];
        }

        // credit card specific
        if ($data['payment_method'] === 'creditcard') {
            $data += [
                'account_type' => $order->billingcardtype,
                'cc_number' => $order->billingcardlastfour,
            ];

            if (strlen($order->billing_card_expiry_year) > 2) {
                $data['cc_expiry'] = $order->billing_card_expiry_month . substr($order->billing_card_expiry_year, 2, 2);
            } else {
                $data['cc_expiry'] = $order->billing_card_expiry_month . $order->billing_card_expiry_year;
            }
        }

        return $this->findOrNewPaymentMethod($data);
    }

    /**
     * Push a donor to DonorPerfect
     *
     * @param array $contact
     * @return \stdClass|bool
     */
    public function findOrNewDonor(array $contact)
    {
        // bail if dp is not connected
        if (! dpo_is_connected()) {
            throw new MessageException('DonorPerfect is not connected.');
        }

        // log what we're pushing to dp
        if (sys_get('dp_logging')) {
            app('log')->channel('donorperfect')->info('Finding or creating donor', [$contact]);
        }

        // find the donor
        $donor = $this->findDonor($contact);

        // if no donor was found, create one
        if (! $donor) {
            // create the donor
            $donor_id = $this->newDonor($contact);

        // otherwise, grab the id
        } else {
            $donor_id = $donor->donor_id;
        }

        // failed
        if (! $donor_id) {
            return false;
        }

        // return the retreived donor_id
        return $this->donor((int) $donor_id);
    }

    /**
     * Push a donor to DonorPerfect
     *
     * @param array $contact
     * @return \stdClass|bool|null
     */
    public function findDonor(array $contact)
    {
        $contact = [
            'email' => trim($contact['email'] ?? ''),
            'first_name' => trim($contact['first_name'] ?? ''),
            'last_name' => trim($contact['last_name'] ?? ''),
            'zip' => trim($contact['zip'] ?? ''),
            'organization' => trim($contact['organization'] ?? ''),
        ];

        // if we don't know their name, zip and email
        if (! $contact['first_name'] && ! $contact['last_name'] && ! $contact['zip'] && ! $contact['email']) {
            // check for ANONYMOUS DONOR
            if (sys_get('dp_anonymous_donor_id')) {
                return $this->donor((int) sys_get('dp_anonymous_donor_id'));
                // otherwise, bail
            }

            return null;
        }

        // //////////////////////////////////////
        // 1. FIND BY EMAIL ADDRESS ONLY
        // //////////////////////////////////////
        if ($this->_isEmailValid($contact['email'])) {
            $matching_donors = app('dpo')->table('dp')
                ->select('dp.*')
                ->whereRaw('LOWER(dp.email) = ?', [strtolower($contact['email'])])->orderBy('dp.donor_id');

            // if organization
            if ($contact['organization']) {
                $matching_donors->where('dp.org_rec', '=', 'Y');
            }

            // include spouse in match
            if (sys_get('dp_match_donor_spouse') == 1 and ! $contact['organization']) {
                $matching_donors->addSelect('dpudf.spouse')
                    ->leftJoin('dpudf', 'dpudf.donor_id', '=', 'dp.donor_id');
            }

            // get the results
            $matching_donors = $this->scopeByDpLink($matching_donors)->get();

            // we found some matches
            if ($matching_donors->count() > 0) {
                // if there is more than one match
                if ($matching_donors->count() === 1) {
                    return $matching_donors->first();
                }

                // try reducing the matches based on first name,
                // or spouse name, or organization name (if they
                // are provided)
                $matching_donors_refined = $matching_donors->filter(function ($donor) use ($contact) {
                    return
                            // INDIVIDUAL - match any first name
                            (
                                ! $contact['organization']
                                and $contact['first_name']
                                and Str::contains(strtolower($donor->first_name), strtolower($contact['first_name']))

                            // INDIVIDIAL - or any spouse name
                            ) or (
                                ! $contact['organization']
                                and sys_get('dp_match_donor_spouse') == 1
                                and $contact['first_name']
                                and Str::contains(strtolower($donor->spouse), strtolower($contact['first_name']))

                            // ORGANIZATION - or organization name
                            ) or (
                                $contact['organization']
                                and Str::contains(strtolower($donor->last_name), strtolower($contact['organization']))
                            );
                });

                // if we fined a refined match, use the first match
                if ($matching_donors_refined->count() > 0) {
                    return $matching_donors_refined->first();
                }

                // otherwise, use the first match from the general
                // search result
                if ($matching_donors->count() > 0) {
                    return $matching_donors->first();
                }
            }
        }

        // //////////////////////////////////////
        // 2. NAME & ZIP
        // //////////////////////////////////////

        // we need a first name, last name and zip to search on
        // (ex: we can't search just on zip and last name)
        if ($contact['first_name'] && $contact['last_name'] && $contact['zip']) {
            // find all zip and lastname matches
            $matching_donors = app('dpo')->table('dp')
                ->select('dp.*')
                ->whereRaw("LOWER(REPLACE(zip,' ','')) LIKE ?", [str_replace(' ', '', strtolower($contact['zip'])) . '%'])
                ->orderBy('dp.donor_id');

            // ORGANIZATION
            if ($contact['organization']) {
                $matching_donors->where('dp.org_rec', '=', 'Y')
                    ->whereRaw('LOWER(dp.last_name) LIKE ?', [strtolower($contact['organization']) . '%']);

            // INDIVIDUAL
            } else {
                $matching_donors->whereRaw('LOWER(dp.last_name) LIKE ?', [strtolower($contact['last_name']) . '%']);

                // include spouse in match
                if (sys_get('dp_match_donor_spouse') == 1) {
                    $matching_donors->addSelect('dpudf.spouse')
                        ->leftJoin('dpudf', 'dpudf.donor_id', '=', 'dp.donor_id');
                }
            }

            // get results
            $matching_donors = $this->scopeByDpLink($matching_donors)->get();

            // if results were returned from DP
            if ($matching_donors->count() > 0) {
                // if, individual, we must match first name
                if (! $contact['organization']) {
                    $match = $matching_donors->filter(function ($donor) use ($contact) {
                        return
                            // match any first name
                            Str::contains(strtolower($donor->first_name), strtolower($contact['first_name']))
                            // or any spouse name
                            or (
                                sys_get('dp_match_donor_spouse') == 1
                                and Str::contains(strtolower($donor->spouse), strtolower($contact['first_name']))
                            );
                    });
                } else {
                    $match = $matching_donors;
                }

                // if we found an exact result, return it
                if ($match->count() > 0) {
                    return $match->first();
                }
            }
        }

        // no matches were made
        // give up and return null
        return null;
    }

    /**
     * Return a donor
     *
     * @param int $donor_id
     * @return \stdClass|null
     */
    public function donor($donor_id)
    {
        return app('dpo')->table('dp')
            ->select('*')
            ->where('donor_id', '=', $donor_id)
            ->get()
            ->first();
    }

    public function getDonorUdfs(int $donorId, array $columns = ['*']): ?\stdClass
    {
        return app('dpo')
            ->table('dpudf')
            ->select($columns)
            ->where('donor_id', $donorId)
            ->first();
    }

    /**
     * Create a new donor and return it.
     *
     * @param array $donorData
     * @return int|bool
     */
    public function newDonor(array $donorData)
    {
        $this->_sanitize($donorData);

        // modify the data if an organization name is passed in
        if (! empty($donorData['organization'])) {
            $donorData['opt_line'] = $donorData['first_name'] . ' ' . $donorData['last_name'];
            $donorData['first_name'] = null;
            $donorData['last_name'] = $donorData['organization'];
            $donorData['org_rec'] = 'Y';
        }

        // generate a salutation
        if (sys_get('dp_sync_salutation') && empty($donorData['salutation'])) {
            if (! empty($donorData['title']) && ! empty($donorData['last_name'])) {
                $donorData['salutation'] = $donorData['title'] . ' ' . $donorData['last_name'];
            } elseif (! empty($donorData['first_name'])) {
                $donorData['salutation'] = $donorData['first_name'];
            }
        }

        if (isset($donorData['phone'])) {
            $donorData[sys_get('dp_phone_mapping')] = $donorData['phone'];
        }

        // create the new donor and retrieve the new id
        $new_donor_id = $this->procedure('dp_savedonor', [
            'donor_id' => 0,                                // numeric Enter 0 (zero) to create a new donor/constituent record or an existing donor_id
            'first_name' => $donorData['first_name'] ?? null, // Nvarchar(100)
            'last_name' => $donorData['last_name'] ?? '',   // Nvarchar(150) ****NOT NULL****
            'middle_name' => null,
            'suffix' => null,
            'title' => $donorData['title'] ?? null, // Nvarchar(100)
            'salutation' => $donorData['salutation'] ?? null,
            'prof_title' => null,
            'opt_line' => $donorData['opt_line'] ?? null, // Nvarchar(100)
            'address' => $donorData['address'] ?? null, // Nvarchar(100)
            'address2' => $donorData['address2'] ?? null, // Nvarchar(100)
            'city' => $donorData['city'] ?? null, // Nvarchar(75)
            'state' => $donorData['state'] ?? null, // Nvarchar(50)
            'zip' => $donorData['zip'] ?? null, // Nvarchar(50)
            'country' => $donorData['country'] ?? null, // Nvarchar(50)
            'address_type' => null,
            'home_phone' => $donorData['home_phone'] ?? null, // Nvarchar(75)
            'business_phone' => $donorData['business_phone'] ?? null, // Nvarchar(75)
            'fax_phone' => $donorData['fax_phone'] ?? null, // Nvarchar(75)
            'mobile_phone' => $donorData['mobile_phone'] ?? null, // Nvarchar(75)
            'email' => $donorData['email'] ?? null, // Nvarchar(100)
            'org_rec' => $donorData['org_rec'] ?? 'N',  // Nvarchar(1), 'Y' (indicating an organizational record) or 'N' (individual)
            'donor_type' => $donorData['donor_type'] ?? null, // Nvarchar(30)
            'nomail' => 'N',                              // Nvarchar(1)
            'nomail_reason' => null,
            'narrative' => null,
            'donor_rcpt_type' => sys_get('dp_default_rcpt_type') ?? null, // Nvarchar(1)
            'user_id' => sys_get('dpo_user_alias'),
            'no_email' => $donorData['no_email'] ?? null, // Nvarchar(1)
        ]);

        // scope using the link table
        if (sys_get('dp_use_link_scope')) {
            app('dpo')->table('dplink')->insert([
                'donor_id' => $new_donor_id,
                'donor_id2' => sys_get('dp_link_donor_id2'),
                'link_code' => sys_get('dp_link_code'),
            ]);
        }

        $donorData['donor_id'] = (int) $new_donor_id;

        // insert each UDF
        if (isset($donorData['udfs']) && count($donorData['udfs']) > 0) {
            $this->updateDonorUdfs($donorData['donor_id'], $donorData['udfs']);
        }

        // return the new donor created
        return ($donorData['donor_id']) ? $donorData['donor_id'] : false;
    }

    /**
     * Save data on a donor.
     *
     * @param int $donor_id
     * @param array $fields
     * @return bool
     */
    public function updateDonor($donor_id, $fields)
    {
        if (! isset($donor_id) || ! is_numeric($donor_id)) {
            throw new MessageException('You must specify a donor id when updating donor fields');
        }

        if (! isset($fields) || empty($fields)) {
            return false;
        }

        unset($fields['donor_id']);

        app('dpo')
            ->table('dp')
            ->where('donor_id', '=', $donor_id)
            ->update($fields);

        return true;
    }

    /**
     * Save data on a donor.
     *
     * @param int $donor_id
     * @param \Ds\Models\GroupAccountTimespan $groupAccount
     * @return bool
     */
    public function updateDonorMembership($donor_id, GroupAccountTimespan $groupAccount)
    {
        if (! isset($donor_id) || ! is_numeric($donor_id)) {
            throw new MessageException('You must specify a donor id when updating donor membership');
        }

        $fields = [
            'mcat' => $groupAccount->group->dp_id,
            'mcat_expire_date' => toLocalFormat($groupAccount->end_date, 'm/d/Y') ?: null,
        ];

        if (sys_get('dp_push_mcat_enroll_date')) {
            $fields['mcat_enroll_date'] = toLocalFormat($groupAccount->start_date, 'm/d/Y') ?: null;
        }

        $this->updateDonorUdfs($donor_id, $fields);

        return true;
    }

    /**
     * Save data on a gift.
     *
     * @param int $gift_id
     * @param array $fields
     * @return bool
     */
    public function updateGift($gift_id, $fields)
    {
        if (! isset($gift_id) || ! is_numeric($gift_id)) {
            throw new MessageException('You must specify a gift id when updating gift fields');
        }

        if (! isset($fields) || empty($fields)) {
            return false;
        }

        unset($fields['gift_id']);

        app('dpo')
            ->table('dpgift')
            ->where('gift_id', '=', $gift_id)
            ->update($fields);

        return true;
    }

    /**
     * Save UDF data against a gift.
     *
     * @param int $gift_id
     * @param array $udfs
     * @return bool
     */
    public function updateGiftUdfs($gift_id, $udfs)
    {
        if (! isset($gift_id) || ! is_numeric($gift_id) || $gift_id < 1) {
            throw new MessageException('You must specify a gift id when updating gift UDFs');
        }

        if (! isset($udfs) || empty($udfs)) {
            return false;
        }

        unset($udfs['gift_id']);

        app('dpo')
            ->table('dpgiftudf')
            ->where('gift_id', '=', $gift_id)
            ->update($udfs);

        return true;
    }

    /**
     * Save UDF data against a donor.
     *
     * @param int $donor_id
     * @param array $udfs
     * @return bool
     */
    public function updateDonorUdfs($donor_id, $udfs)
    {
        if (! isset($donor_id) || ! is_numeric($donor_id)) {
            throw new MessageException('You must specify a donor id when updating donor UDFs');
        }

        if (! isset($udfs) || empty($udfs)) {
            return false;
        }

        unset($udfs['donor_id']);

        app('dpo')
            ->table('dpudf')
            ->where('donor_id', '=', $donor_id)
            ->update($udfs);

        return true;
    }

    /**
     * Return a gift with some relationships connected.
     *
     * @param int $gift_id
     * @return \stdClass|bool
     */
    public function gift($gift_id)
    {
        $gift = app('dpo')->table('dpgift')
            ->select('*')
            ->where('gift_id', '=', $gift_id)
            ->get()
            ->first();

        // no gift, bail
        if (! $gift) {
            return false;
        }

        $gift->udfs = app('dpo')->table('dpgiftudf')
            ->select('*')
            ->where('gift_id', '=', $gift->gift_id)
            ->get()
            ->first();

        $gift->donor = app('dpo')->table('dp')
            ->select('*')
            ->where('donor_id', '=', $gift->donor_id)
            ->get()
            ->first();

        $gift->split_gifts = app('dpo')->table('dpgift')
            ->select('*')
            ->where('glink', '=', $gift->gift_id)
            ->get();

        // if this gift is a pledge payment, grab the pledge
        $gift->pledge = ($gift->plink) ? app('dpo')->table('dpgift')
            ->select('*')
            ->where('gift_id', '=', $gift->plink)
            ->get()
            ->first() : null;

        // for every split gift, grab more detail
        // we don't recurse because it's just too much
        // network traffic - we only want the gift details
        foreach ($gift->split_gifts as &$split) {
            // grab the udfs
            $split->udfs = app('dpo')->table('dpgiftudf')
                ->select('*')
                ->where('gift_id', '=', $split->gift_id)
                ->get();

            // if the split is a pledge payment, grab the pledge
            $split->pledge = ($split->plink) ? app('dpo')->table('dpgift')
                ->select('*')
                ->where('gift_id', '=', $split->plink)
                ->get()
                ->first() : null;
        }

        return $gift;
    }

    public function giftExists($gift_id): bool
    {
        return app('dpo')->table('dpgift')
            ->select('gift_id')
            ->where('gift_id', '=', $gift_id)
            ->get()
            ->isNotEmpty();
    }

    /**
     * Get Gifts by a specific donor
     *
     * @param int $donor_id
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Support\Collection
     */
    public function getGiftsByDonor(int $donor_id, int $resultsPerPage = 0, string $start_date = null, string $end_date = null, string $gl_codes = null, string $gift_types = null)
    {
        $start_date = fromLocal($start_date);
        $end_date = fromLocal($end_date);

        $gifts = app('dpo')->table('dpgift')
            ->select('gift_id', 'gift_date', 'currency', 'fmv', 'rcpt_num', 'amount', 'reference', 'gl_code', 'gift_type')
            ->where('donor_id', '=', $donor_id)
            ->where('record_type', '=', 'G')
            ->orderBy('gift_date', 'desc');

        if ($start_date) {
            $gifts->where('gift_date', '>=', $start_date->format('Y-m-d'));
        }
        if ($end_date) {
            $gifts->where('gift_date', '<=', $end_date->format('Y-m-d'));
        }

        if ($gl_codes) {
            $gifts->whereIn('gl_code', array_map('trim', explode(',', $gl_codes)));
        }

        if ($gift_types) {
            $gifts->whereIn('gift_type', array_map('trim', explode(',', $gift_types)));
        }

        if ($resultsPerPage == 0) {
            $records = collect();
            $gifts->chunk(500, function ($gifts) use (&$records) {
                $records = $records->merge($gifts);
            });

            return $records;
        }

        return $gifts->paginate($resultsPerPage, ['*'], 'all_history_page');
    }

    public function getGiftByReference(string $reference, string $select = '*'): ?\stdClass
    {
        return app('dpo')->table('dpgift')
            ->select($select)
            ->where('gift_narrative', 'LIKE', '%[[]' . $reference . ']%')
            ->get()
            ->first();
    }

    public function getGiftIdByReference(string $reference): ?int
    {
        $gift = $this->getGiftByReference($reference, 'gift_id');

        return $gift->gift_id ?? null;
    }

    public function createOrUpdateGift(array $gift_data): int
    {
        if (empty($gift_data['gc_reference'])) {
            return $this->createNewGift($gift_data);
        }

        $gift_id = app(ExternalReferencesService::class)->getReferenceByCoding($gift_data['gc_reference']);

        if ($gift_id && ! $this->giftExists($gift_id)) {
            $this->cleanDefunktGiftByCoding($gift_data['gc_reference']);
            $gift_id = null;
        }

        if (! $gift_id) {
            $gift_id = $this->getGiftIdByReference($gift_data['gc_reference']);
        }

        if (! $gift_id) {
            return $this->createNewGift($gift_data);
        }

        return $this->updateGiftFromPositionalParams($gift_id, $gift_data);
    }

    protected function cleanDefunktGiftByCoding(string $coding): void
    {
        app(ExternalReferencesService::class)->deleteByCoding($coding);
    }

    /**
     * Create a new gift
     *
     * @param array $gift_data
     * @return array
     */
    public function newGift(array $gift_data)
    {
        $this->_sanitize($gift_data);

        $gift_udf_updates = [];

        // format gift narrative
        if (isset($gift_data['gc_invoice_number'])) {
            if (isset($gift_data['gift_narrative']) && trim($gift_data['gift_narrative']) !== '') {
                $gift_data['gift_narrative'] = trim($gift_data['gift_narrative']) . ' (GC #' . $gift_data['gc_invoice_number'] . ')';
            } else {
                $gift_data['gift_narrative'] = 'GC #' . $gift_data['gc_invoice_number'];
            }
        }

        if (isset($gift_data['gc_reference'])) {
            $gift_data['gift_narrative'] .= ' [' . $gift_data['gc_reference'] . ']';
        }

        if (isset($gift_data['gift_narrative_2'])) {
            $gift_data['gift_narrative'] .= "\n\n" . $gift_data['gift_narrative_2'];
        }

        // if we issued a tax receipt, we need to update the gift
        if (feature('tax_receipt')
            && sys_get('tax_receipt_pdfs') == 1
            && isset($gift_data['rcpt_num'])) {
            $gift_data['rcpt_type'] = 'I';
            $gift_data['rcpt_date'] = $gift_data['rcpt_date'] ?? null;
            $gift_data['rcpt_num'] = $gift_data['rcpt_num'] ?? null;
            $gift_data['rcpt_status'] = 'RCPT';
            $gift_udf_updates['rcpt_amount'] = $gift_data['rcpt_amount'] ?? 0;
            $gift_data['receipt_delivery_g'] = 'E';
            $gift_data['emailsentty_date'] = $gift_data['rcpt_date'] ?? null;
            $gift_data['ty_date'] = $gift_data['rcpt_date'] ?? null;

        // otherwise, set rcpt_type based on integration settings
        } elseif (trim(sys_get('dp_default_rcpt_type')) !== '') {
            $gift_data['rcpt_type'] = trim(sys_get('dp_default_rcpt_type'));
            $gift_data['rcpt_date'] = null;
            $gift_data['rcpt_num'] = null;
            $gift_data['rcpt_status'] = null;
            $gift_udf_updates['rcpt_amount'] = null;
            if (trim(sys_get('dp_default_rcpt_pref'))) {
                $gift_data['receipt_delivery_g'] = trim(sys_get('dp_default_rcpt_pref'));
            }
        }

        // set currency
        if (trim(sys_get('dpo_currency')) !== '' && ! isset($gift_data['currency'])) {
            $gift_data['currency'] = sys_get('dpo_currency');
        }

        // map gift_type from UDFs if necessary...
        // use case: a client wants to map data to columns
        // on the actual gift table.
        if (isset($gift_data['udfs'])) {
            $mappable_udf_cols = ['gift_type', 'solicit_code', 'sub_solicit_code', 'campaign', 'fmv'];
            foreach ($mappable_udf_cols as $col) {
                if (array_key_exists($col, $gift_data['udfs'])) {
                    if (! (array_key_exists($col, $gift_data) and ! empty($gift_data[$col])) or ! sys_get('dp_product_codes_override')) {
                        $gift_data[$col] = $gift_data['udfs'][$col];
                    }
                    unset($gift_data['udfs'][$col]);
                }
            }
        }

        // strict code value verification
        $verifiable_gift_cols = ['gift_type', 'solicit_code', 'sub_solicit_code', 'campaign'];
        foreach ($verifiable_gift_cols as $col) {
            if (array_key_exists($col, $gift_data)) {
                $gift_data[$col] = $this->verifiedCode($col, $gift_data[$col]);
            }
        }

        // DP now requires the following be NULL instead of blank values
        $nullable_gift_cols = ['campaign', 'gl', 'glink', 'plink', 'solicit_code', 'sub_solicit_code', 'ty_letter_no'];
        foreach ($nullable_gift_cols as $col) {
            if (empty($gift_data[$col])) {
                $gift_data[$col] = null;
            }
        }

        // push gift to dp
        $gift_data['gift_id'] = $this->createOrUpdateGift($gift_data);

        // if we got a new_gift_id from DP
        if (! $gift_data['gift_id']) {
            throw new MessageException('No gift was created.');
        }
        // For later use, 'gift_id' needs to be an integer.
        $gift_data['gift_id'] = (int) $gift_data['gift_id'];

        // insert each UDF
        if (isset($gift_data['udfs']) && count($gift_data['udfs']) > 0) {
            $gift_udf_updates = array_merge($gift_udf_updates, $gift_data['udfs']);
        }

        // update gift & udfs
        $this->updateGiftUdfs($gift_data['gift_id'], $gift_udf_updates);

        // create the tribute
        if (isset($gift_data['tribute']) && ! empty($gift_data['tribute'])) {
            // this API requires a CODE_ID from the dpcodes table
            $dp_tribute_type = app('dpo')->table('dpcodes')
                ->select('code_id', 'code', 'description')
                ->where('code', '=', $gift_data['tribute']['code'])
                ->where('field_name', '=', 'MEMORY_HONOR')
                ->get()
                ->first();

            // if we find a matching type
            if ($dp_tribute_type) {
                // save tribute details to gift narrative
                if (sys_get('dp_tribute_details_to_narrative') || sys_get('dp_tribute_message_to_narrative')) {
                    // update narrative
                    $msgs = [];
                    if (sys_get('dp_tribute_details_to_narrative') == 1) {
                        $msgs[] = 'Tribute to: ' . $gift_data['tribute']['name'] . ' (' . $dp_tribute_type->description . ')';
                        if (isset($gift_data['tribute']['notify_donor'])) {
                            $addy = address_format($gift_data['tribute']['notify_donor']['address'], null, $gift_data['tribute']['notify_donor']['city'], $gift_data['tribute']['notify_donor']['state'], $gift_data['tribute']['notify_donor']['zip'], $gift_data['tribute']['notify_donor']['country'], ', ');
                            $msgs[] = 'Notify: ' . $gift_data['tribute']['notify_donor']['name']
                                 . (($gift_data['tribute']['notify_donor']['email']) ? ', ' . $gift_data['tribute']['notify_donor']['email'] : '')
                                 . (($addy) ? ', ' . $addy : '');
                        }
                    }
                    if (sys_get('dp_tribute_message_to_narrative') == 1 && $gift_data['tribute']['message']) {
                        $msgs[] = 'Tribute Message: ' . $gift_data['tribute']['message'];
                    }
                    $gift_data['gift_narrative'] = $gift_data['gift_narrative'] . "\n\n" . implode("\n", $msgs);

                    // save narrative
                    $this->updateGift($gift_data['gift_id'], [
                        'gift_narrative' => $gift_data['gift_narrative'],
                    ]);
                }

                // create the tribute (returns an array for some reason)
                $tributes = $this->procedure('dp_tribAnon_Create', [
                    $gift_data['tribute']['name'], // @Name "Uncle Frank"
                    $dp_tribute_type->code_id,     // @Code_ID
                    1,                             // @Active_Flag
                    $gift_data['date'],            // @UserCreateDt (order date)
                    null,                           // @Recipients (global recipients - we never use this)
                ]);

                // if we received some goodness, lets grab the first tribute
                // and keep trucking
                if ($tributes) {
                    $tribute = $tributes[0];
                }

                // save to log
                $gift_data['tribute']['tribute_id'] = $tribute->tributeid;

                // link the tribute to the gift
                $this->procedure('dp_tribAnon_AssocTribsToGift', [
                    $gift_data['gift_id'],  // @Gift_ID
                    $tribute->tributeid,    // @Tribute_ID
                ]);

                // if there is a recipient
                // ("Aunt Sue" is receiving the email - Frank is dead)
                if (isset($gift_data['tribute']['notify_donor'])) {
                    // first name / last name issues ("Aunt Sue")
                    $first_name = '';
                    $last_name = '';

                    // if there is name entered
                    if ($gift_data['tribute']['notify_donor']['name']) {
                        // break it into pieces
                        $names = explode(' ', $gift_data['tribute']['notify_donor']['name']);

                        // if there's one piece, use it as first name
                        if (count($names) === 1) {
                            $first_name = $names[0];

                        // if there are multiple pieces, use the LAST
                        // piece as the last_name, and cram everthing
                        // else in the first name
                        } else {
                            $last_name = array_pop($names);
                            $first_name = implode(' ', $names);
                        }
                    }

                    // findOrNewDonor
                    $recipient_donor = $this->findOrNewDonor(array_merge($gift_data['tribute']['notify_donor'], [
                        'first_name' => $first_name,
                        'last_name' => $last_name,
                        'email' => $gift_data['tribute']['notify_donor']['email'] ?? null,
                    ]));

                    if (! $recipient_donor) {
                        throw new MessageException('No donor to use.');
                    }

                    // save to log
                    $gift_data['tribute']['notify_donor']['donor_id'] = $recipient_donor->donor_id;

                    // assign receipient to tribute
                    $this->procedure('dp_tribAnon_SaveTribRecipient', [
                        'DonorId' => $recipient_donor->donor_id,   // recipient being added to the tribute
                        'TributeID' => $tribute->tributeid,          // the ID of the tribute
                        'GiftID' => $gift_data['gift_id'],        // the Gift ID
                        'Level' => 'L',                          // should always be L
                    ]);

                    // create a tribute gift notification record
                    $this->procedure('dp_tribNotif_Save', [
                        'Gift_ID' => 0,
                        'Donor_Id' => $recipient_donor->donor_id,
                        'glink' => $gift_data['gift_id'],
                        'tlink' => $tribute->tributeid,
                        'amount' => $gift_data['amount'],
                        'total' => $gift_data['amount'],
                        'bill' => '0.00',
                        'start_date' => null,
                        'frequency' => null,
                        'gift_type' => 'SN',
                        'record_type' => 'N',
                        'gl_code' => $gift_data['gl'],
                        'solicit_code' => $gift_data['solicit_code'],
                        'sub_solicit_code' => $gift_data['sub_solicit_code'],
                        'campaign' => $gift_data['campaign'],
                        'ty_letter_no' => 'NT',
                        'fmv' => $gift_data['fmv'],
                        'reference' => $gift_data['transaction_id'],
                        'gfname' => null,
                        'glname' => null,
                        'gift_narrative' => $gift_data['gift_narrative'],
                        'membership_type' => null,
                        'membership_level' => null,
                        'membership_enr_date' => null,
                        'membership_exp_date' => null,
                        'address_id' => '0',
                        'user_id' => sys_get('dpo_user_alias'),
                    ]);
                }
            }
        }

        // create a soft credit
        if (! empty($gift_data['soft_credits'])) {
            foreach ($gift_data['soft_credits'] as $donor_id => $soft_credit) {
                $gift_data['soft_credits'][$donor_id] = $this->newGift([
                    'donor_id' => $soft_credit['donor_id'],
                    'record_type' => 'S',
                    'glink' => $gift_data['gift_id'],
                    'date' => $gift_data['date'],
                    'amount' => $gift_data['amount'],
                    'currency' => $gift_data['currency'] ?? sys_get('dpo_currency'),
                    'gl' => $gift_data['gl'],
                    'solicit_code' => $gift_data['solicit_code'],
                    'sub_solicit_code' => $gift_data['sub_solicit_code'],
                    'gift_type' => 'SC', // $gift_data['gift_type'],
                    'campaign' => $gift_data['campaign'],
                    'no_calc' => $gift_data['no_calc'],
                ]);
            }
        }

        // Trigger calculated fields
        $this->updateCalculatedFields($gift_data['gift_id'], $gift_data['donor_id'] ?? null);

        // return the new gift created
        return ($gift_data['gift_id']) ? $gift_data : false;
    }

    /**
     * Create a new pledge.
     *
     * This is done by creating a gift and CONVERTING it
     * into a pledge.
     *
     * @param array $data
     * @return array
     */
    public function newPledge(array &$data)
    {
        $data['record_type'] = 'P';
        $data['amount'] = 0.00;

        if (isset($data['glink'])) {
            unset($data['glink']);
        }

        if (isset($data['split_gift'])) {
            unset($data['split_gift']);
        }

        // create the gift
        $pledge = $this->newGift($data);

        // convert gift to a pledge
        $this->updateGift($pledge['gift_id'], [
            'record_type' => 'P',
            'amount' => 0.00,
            'start_date' => $data['start_date'],
            'bill' => $data['bill'],
            'initial_payment' => 'N', // not sure what this is
            'frequency' => $data['frequency'],
            'vault_id' => $data['vault_id'] ?? null,
        ]);

        // push udfs
        if ($pledge['udfs']) {
            $this->updateGiftUdfs($pledge['gift_id'], $pledge['udfs']);
        }

        // return the new pledge id
        return $pledge;
    }

    public function findOrNewPaymentMethod(array $data): ?array
    {
        $this->_sanitize($data);

        $paymentMethod = $this->findPaymentMethod((int) $data['donor_id'], (string) $data['vault_id']);

        if (isset($paymentMethod->dppaymentmethodid)) {
            return array_merge($data, ['id' => $paymentMethod->dppaymentmethodid]);
        }

        $result = $this->procedure('dp_paymentmethod_insert', [
            $data['vault_id'] ?? null,                                // vaultid
            $data['donor_id'] ?? null,                                // donor_id
            (($data['is_default'] ?? 0) == 1) ? 1 : 0,                // is_default
            $data['account_type'] ?? null,                            // account type (vi, mc, bank account, etc)
            $data['payment_method'] ?? null,                          // method (creditcard,check)

            // credit card
            $data['cc_number'] ?? null,                               // credit card number (last 4)
            $data['cc_expiry'] ?? null,                               // expiry

            // check
            $data['account_last_four'] ?? null,                       // account number (last 4)
            $data['name_on_account'] ?? null,                         // name on account

            $data['created_date'] ?? toLocalFormat('now', 'm\/d\/Y'), // created
            $data['modified_date'] ?? null,                           // modified
            $data['import_id'] ?? null,                               // import_id
            $data['created_by'] ?? sys_get('dpo_user_alias'),         // created by
            $data['modified_by'] ?? null,                             // modified by
            $data['currency'] ?? sys_get('dpo_currency'),              // selected_currency
        ]);

        if (count($result) > 0) {
            $data['id'] = $result[0]->dppaymentmethodid;
        }

        // return the new gift created
        return ($data['id']) ? $data : null;
    }

    public function findPaymentMethod(int $donorId, string $customerVaultId): ?\stdClass
    {
        return app('dpo')->table('dppaymentmethod')
            ->select('*')
            ->where('donor_id', '=', $donorId)
            ->where('customervaultid', '=', $customerVaultId)
            ->get()
            ->first();
    }

    /**
     * Adjust a gift
     *
     * Increasing or decreasing the amount of a gift.
     *
     * @param int $original_gift_id
     * @param float $adjustment_amount The amount of the adjustment. Refunds should be negative.
     * @param string $adjustment_date Date of the adjustment
     * @return array
     */
    public function adjustGift(int $original_gift_id, float $adjustment_amount, string $adjustment_date = null)
    {
        $original_gift = $this->gift($original_gift_id);

        if (! $original_gift) {
            throw new MessageException("Gift ID '$original_gift_id' was not found in DP.");
        }

        if (! isset($adjustment_date)) {
            $adjustment_date = toLocalFormat('now', 'm/d/Y');
        }

        // ========================================
        // step 1: change original gift from G to A
        $this->updateGift($original_gift_id, [
            'record_type' => 'A',
            'modified_by' => sys_get('dpo_user_alias'),
            'modified_date' => $adjustment_date,
        ]);

        // ========================================
        // step 2: create a gift that adjusts the gift id

        // original gift data that should remain consistent
        // throughout all adjustment / balance gifts created
        $original_gift_data = [
            'donor_id' => $original_gift->donor_id,
            'currency' => $original_gift->currency,
            'gl' => $original_gift->gl_code,
            'solicit_code' => $original_gift->solicit_code,
            'sub_solicit_code' => $original_gift->sub_solicit_code,
            'campaign' => $original_gift->campaign,
            'gift_type' => $original_gift->gift_type,
            'rcpt_type' => $original_gift->rcpt_type,
            'receipt_delivery_g' => $original_gift->receipt_delivery_g,
            'fmv' => $original_gift->fmv,
            'split_gift' => $original_gift->split_gift,
            'pledge_payment' => $original_gift->pledge_payment,
            'no_calc' => $original_gift->nocalc,
            'gift_narrative' => $original_gift->gift_narrative,
        ];

        // data specific to adjustment
        $adjustment_gift = array_merge($original_gift_data, [
            'date' => $adjustment_date,
            'glink' => $original_gift->gift_id, // GLINK for adjusted gift
            'record_type' => 'H',
            'amount' => $adjustment_amount,
            'created_by' => sys_get('dpo_user_alias'),
        ]);

        // create adjustment
        $adustment_gift['gift_id'] = $this->newGift($adjustment_gift);

        // ========================================
        // step 3: create a balance gift that represents the balance remaining
        $balance_gift = array_merge($original_gift_data, [
            'date' => $original_gift->gift_date,
            'alink' => $original_gift->gift_id, // ALINK for final balance
            'record_type' => 'G',
            'amount' => (float) $original_gift->amount + $adjustment_amount,
            'created_by' => sys_get('dpo_user_alias'),
        ]);

        // if its a full refund (or void), we need to set record_type to 'C'
        if ($original_gift->amount + $adjustment_amount == 0) {
            $balance_gift['record_type'] = 'C';
            $balance_gift['amount'] = 0;
        }

        // create balance
        $balance_gift['gift_id'] = $this->newGift($balance_gift);

        if (sys_get('dp_logging')) {
            app('log')->channel('donorperfect')->info('Pushed adjustment to DP', [$adjustment_gift, $balance_gift]);
        }

        return [$adjustment_gift, $balance_gift];
    }

    /**
     * Help prevent "test" and "noemail" emails from being used
     * to link existing donors.
     *
     * @param string $email
     * @return bool
     */
    private function _isEmailValid(string $email)
    {
        return trim($email) !== ''
            && strpos($email, '@none.') === false
            && strpos($email, '@asdf.') === false
            && strpos($email, '@nomail.') === false
            && strpos($email, '@noemail.') === false
            && strpos($email, '@test.') === false;
    }

    /**
     * Stripping all blank strings and white space from values.
     *
     * @param mixed $var
     */
    private function _sanitize(&$var)
    {
        if (is_string($var)) {
            $var = (trim($var)) ? trim($var) : null;
        } elseif (is_a($var, 'Carbon\Carbon')) {
            $var = $var->format('m/d/Y');
        } elseif (is_array($var)) {
            foreach ($var as &$v) {
                $this->_sanitize($v);
            }
        }
    }

    /**
     * Returns an array of all available custom fields that have
     * been configured for this site.
     *
     * Example:
     * ['meta9','meta10']
     *
     * @return array
     */
    private function _customFields()
    {
        static $fields;

        if (isset($fields)) {
            return $fields;
        }

        $fields = collect(range(9, 22))
            ->map(function ($key) {
                return "meta$key";
            })->reject(function ($key) {
                return empty(sys_get("dp_{$key}_field")) || empty(sys_get("dp_{$key}_label"));
            })->all();

        return $fields;
    }

    public function getReferenceCoding($model, $type = null): string
    {
        $object = ExternalReferenceType::ORDER;

        if (is_a($model, Transaction::class)) {
            $object = ExternalReferenceType::TXN;
        }

        if (is_a($model, OrderItem::class)) {
            $object = ExternalReferenceType::ITEM;
        }

        return implode(':', array_filter([
            'GC', site()->id, $object, $type, $model->getKey(),
        ]));
    }

    /**
     * Helper for running a DP procedure.
     *
     * @param string $proc
     * @param array $params
     * @return array|int
     */
    public function procedure($proc, array $params)
    {
        return app('dpo')->request($proc, $this->_paramStr($params));
    }

    protected function createNewGift($gift_data): int
    {
        return (int) $this->procedure('dp_savegift', [
            0,                                       // @gift_id numeric Enter 0 in this field to create a new gift or the gift ID of an existing gift.
            $gift_data['donor_id'] ?? null,          // @donor_id numeric
            $gift_data['record_type'] ?? 'G',        // @record_type Nvarchar(30) ‘G’ for Gift, ‘P’ for Pledge
            $gift_data['date'] ?? null,              // @gift_date datetime
            $gift_data['amount'] ?? 0,               // @amount money
            $gift_data['gl'] ?? null,                // @gl_code Nvarchar(30)
            $gift_data['solicit_code'] ?? null,      // @solicit_code Nvarchar(30)
            $gift_data['sub_solicit_code'] ?? null,  // @sub_solicit_code Nvarchar(30)
            $gift_data['gift_type'] ?? null,         // @gift_type Nvarchar(30)
            $gift_data['split_gift'] ?? 'N',         // @split_gift Nvarchar(1)
            $gift_data['pledge_payment'] ?? 'N',     // @pledge_payment Nvarchar(1)
            $gift_data['check_ref_number'] ?? null,  // @reference Nvarchar(25)
            null,                                    // @memory_honor Nvarchar(30)
            null,                                    // @gfname Nvarchar(50)
            null,                                    // @glname Nvarchar(75)
            $gift_data['fmv'] ?? 0,                  // @fmv money
            0,                                       // @batch_no numeric
            $gift_data['gift_narrative'] ?? null,    // @gift_narrative Nvarchar(3000)
            $gift_data['ty_letter_no'] ?? null,      // @ty_letter_no Nvarchar(30)
            $gift_data['glink'] ?? null,             // @glink numeric
            $gift_data['plink'] ?? null,             // @plink numeric If recurring, set the plink value of the gift to the gift_ID value of the associated pledge.
            $gift_data['no_calc'] ?? 'N',            // @nocalc Nvarchar(1)
            'N',                                     // @receipt Nvarchar(1)
            null,                                    // @old_amount money
            sys_get('dpo_user_alias'),               // @user_id Nvarchar(20) We recommend that you use a name here
            $gift_data['campaign'] ?? null,          // @campaign Nvarchar(30) = NULL
            null,                                    // @membership_type Nvarchar(30) = NULL
            null,                                    // @membership_level Nvarchar(30) = NULL
            null,                                    // @membership_enr_date datetime = NULL
            null,                                    // @membership_exp_date datetime = NULL
            null,                                    // @membership_link_ID numeric = NULL
            null,                                    // @address_id numeric = NULL,
            $gift_data['rcpt_status'] ?? null,       // @rcpt_status => 'nvarchar:20',
            $gift_data['rcpt_type'] ?? null,         // @rcpt_type => 'nvarchar:1',
            null,                                    // @membership_code => 'nvarchar:20',
            null,                                    // @contact_id => 'numeric',
            $gift_data['receipt_delivery_g'] ?? null, // @receipt_delivery_g => 'nvarchar:30',
            null,                                    // @TempReturnTableNam => 'varchar:65',
            null,                                    // @gift_aid_amt => 'money',
            $gift_data['alink'] ?? null,             // @alink => 'numeric',
            null,                                    // @batch_gift_id => 'numeric',
            $gift_data['tlink'] ?? null,             // @tlink => 'numeric',
            null,                                    // @GA_origid => 'numeric',
            null,                                    // @import_id => 'numeric',
            null,                                    // @eftbatch => 'numeric',
            null,                                    // @starting_bid => 'money',
            null,                                    // @auction_item_no => 'numeric',
            null,                                    // @bundle_id => 'numeric',
            null,                                    // @event_id => 'numeric',
            $gift_data['transaction_id'] ?? null,    // @transaction_id => 'numeric',
            null,                                    // @ga_pending => 'numeric',
            null,                                    // @delinquentEFT => 'money',
            null,                                    // @wl_import_id => 'numeric',
            null,                                    // @eft_sync_id => 'bigint',
            null,                                    // @eftbatch2 => 'numeric',
            null,                                    // @line_id => 'numeric',
            $gift_data['ty_date'] ?? null,           // @ty_date => 'datetime',
            $gift_data['rcpt_date'] ?? null,         // @RCPT_DATE => 'datetime',
            null,                                    // @gift_aid_date => 'datetime',
            null,                                    // @GA_timestmp => 'datetime',
            $gift_data['emailsentty_date'] ?? null,  // @EmailSentTY_Date => 'datetime',
            null,                                    // @LetterSentTY_Date => 'datetime',
            null,                                    // @lasteftattemptdate => 'datetime',
            null,                                    // @first_gift => 'varchar:1',
            null,                                    // @gift_aid_eligible_g => 'char:1',
            $gift_data['currency'] ?? null,          // @currency => 'varchar:3',
            $gift_data['acknowledgepref'] ?? null,   // @ACKNOWLEDGEPREF => 'varchar:3',
            null,                                    // @auction_sold => 'varchar:20',
            null,                                    // @GA_Runby => 'varchar:20',
            $gift_data['rcpt_num'] ?? null,          // @RCPT_NUM => 'varchar:200',
            null,                                    // @eft_status_description => 'varchar:255',
            null,                                    // @auction_category => 'varchar:100',
            null,                                    // @CF_FRNAME => 'nvarchar:125',
            null,                                    // @CF_PAGENAME => 'nvarchar:50',
            null,                                    // @CF_URL => 'nvarchar:200',
            null,                                    // @QuickBooksPostId => 'numeric',
            null,                                    // @QuickBooksPostDate => 'datetime',
            null,                                    // @cfOrganizationFormid => 'numeric',
            null,                                    // @cfIndividualFormid => 'numeric',
            null,                                    // @update_amount => 'varchar:1',
        ]);
    }

    protected function updateGiftFromPositionalParams($gift_id, $gift_data): int
    {
        $updated = $this->updateGift($gift_id, array_filter([
            'donor_id' => $gift_data['donor_id'] ?? null, // @donor_id numeric
            'record_type' => $gift_data['record_type'] ?? 'G', // @record_type Nvarchar(30) ‘G’ for Gift, ‘P’ for Pledge
            'gift_date' => $gift_data['date'] ?? null, // @gift_date datetime
            'amount' => $gift_data['amount'] ?? 0, // @amount money
            'gl_code' => $gift_data['gl'] ?? null, // @gl_code Nvarchar(30)
            'solicit_code' => $gift_data['solicit_code'] ?? null, // @solicit_code Nvarchar(30)
            'sub_solicit_code' => $gift_data['sub_solicit_code'] ?? null, // @sub_solicit_code Nvarchar(30)
            'gift_type' => $gift_data['gift_type'] ?? null, // @gift_type Nvarchar(30)
            'split_gift' => $gift_data['split_gift'] ?? 'N', // @split_gift Nvarchar(1)
            'pledge_payment' => $gift_data['pledge_payment'] ?? 'N', // @pledge_payment Nvarchar(1)
            'reference' => $gift_data['check_ref_number'] ?? null, // @reference Nvarchar(25)
            'fmv' => $gift_data['fmv'] ?? 0, // @fmv money
            'gift_narrative' => $gift_data['gift_narrative'] ?? null, // @gift_narrative Nvarchar(3000)
            'ty_letter_no' => $gift_data['ty_letter_no'] ?? null, // @ty_letter_no Nvarchar(30)
            'glink' => $gift_data['glink'] ?? null, // @glink numeric
            'plink' => $gift_data['plink'] ?? null, // @plink numeric If recurring, set the plink value of the gift to the gift_ID value of the associated pledge.
            'nocalc' => $gift_data['no_calc'] ?? 'N', // @nocalc Nvarchar(1)
            'campaign' => $gift_data['campaign'] ?? null, // @campaign Nvarchar(30) = NULL
            'rcpt_status' => $gift_data['rcpt_status'] ?? null, // @rcpt_status => 'nvarchar:20',
            'rcpt_type' => $gift_data['rcpt_type'] ?? null, // @rcpt_type => 'nvarchar:1',
            'receipt_delivery_g' => $gift_data['receipt_delivery_g'] ?? null, // @receipt_delivery_g => 'nvarchar:30',
            'alink' => $gift_data['alink'] ?? null, // @alink => 'numeric',
            'tlink' => $gift_data['tlink'] ?? null, // @tlink => 'numeric',
            'transaction_id' => $gift_data['transaction_id'] ?? null, // @transaction_id => 'numeric',
            'ty_date' => $gift_data['ty_date'] ?? null, // @ty_date => 'datetime',
            'rcpt_date' => $gift_data['rcpt_date'] ?? null, // @RCPT_DATE => 'datetime',
            'emailsentty_date' => $gift_data['emailsentty_date'] ?? null, // @EmailSentTY_Date => 'datetime',
            'currency' => $gift_data['currency'] ?? null, // @currency => 'varchar:3',
            'acknowledgepref' => $gift_data['acknowledgepref'] ?? null, // @ACKNOWLEDGEPREF => 'varchar:3',
            'rcpt_num' => $gift_data['rcpt_num'] ?? null, // @RCPT_NUM => 'varchar:200',
        ], function ($key) use ($gift_data) {
            $keyMap = [
                'nocalc' => 'no_calc',
                'reference' => 'check_ref_number',
                'gl_code' => 'gl',
                'gift_date' => 'date',
            ];

            return array_key_exists($keyMap[$key] ?? $key, $gift_data);
        }, ARRAY_FILTER_USE_KEY));

        return $updated ? $gift_id : 0;
    }

    /**
     * Filter NULLs from an array.
     *
     * @param array $arr
     * @return array
     */
    private function filterNulls(array $arr)
    {
        return array_filter($arr, function ($value) {
            return $value !== null;
        });
    }

    /**
     * Takes an array of params for a DPO request and converts
     * them to an sql safe string of comma separated values.
     *
     * Example:
     *
     * (array) ['meta9', 'meta10', 123.03, null, null, 'test']
     *
     * converts to
     *
     * (string) "'meta9','meta10',123.03,null,null,'test'"
     *
     * @param array $params
     * @return string
     */
    private function _paramStr(array $params)
    {
        $namedParams = Arr::isAssoc($params);

        foreach ($params as $key => &$param) {
            if ($param === null) {
                $param = 'null';
            } elseif (is_float($param)) {
                $param = sprintf('%f', $param);
            } elseif (is_int($param)) {
                $param = sprintf('%d', $param);
            } else {
                $param = "'" . str_replace("'", "''", $param) . "'";
            }

            if ($namedParams) {
                $param = "@{$key}=$param";
            }
        }

        return implode(',', array_values($params));
    }

    /**
     * Add a value to the end of a comma separated string.
     * (used for adding ids to order.alt_transion_ids)
     */
    private function appendToCsvStr($str = null, $val = null)
    {
        if (! $str) {
            $values = [];
        } else {
            $values = explode(',', $str);
        }

        $values[] = $val;

        return implode(',', array_unique($values));
    }

    /**
     * Verify codes before they are pushed into DP.
     *
     * @param string $field_name
     * @param string $value
     * @param bool $force_create
     * @return string|null
     */
    public function verifiedCode($field_name, $value = null, $force_create = false)
    {
        if (empty($value)) {
            return null;
        }

        // grab all possible codes for that field
        // type in DP and cache it for 10min
        $codes = Cache::remember("verified-$field_name", now()->addMinutes(1), function () use ($field_name) {
            $src_codes = $this->getCodes($field_name, false);

            // if no results, return null
            return (count($src_codes) > 0) ? $src_codes : null;
        });

        // if no results, assume the value passed in is good
        // (could be a network error)
        if (! is_array($codes)) {
            return $value;
        }

        // clean string
        $cleaned = Str::slug($value, '_');

        // if the value provided matches return the code
        foreach ($codes as $code) {
            if (strtolower($code->code) === strtolower($value) || strtolower($code->code) === $cleaned) {
                return $code->code;
            }
        }

        // search through the the code descriptions and see if there
        // is a match (example: gift_type: "Visa" may be the code "V" or "VI")
        // do it case insensitive so that FACEBOOK matches Facebook
        foreach ($codes as $code) {
            if (strtolower($code->description) === strtolower($value) || strtolower($code->description) === $cleaned) {
                return $code->code;
            }
        }

        // create the code in DP if it doesn't exist
        // by this point in the code
        if ($force_create) {
            return $this->createCode($field_name, $value);
        }

        // otherwise, return null (push no value to DP)
        return null;
    }

    /**
     * Create a code in DP.
     *
     * app('Ds\Services\DonorPerfectService')->createCode("solicit_code","joshs-30th-birthday-party","joshs-30th-birthday-party");
     *
     * @param string $field_name
     * @param string $code
     * @param string $description
     * @return string
     */
    public function createCode($field_name, $code, $description = null)
    {
        // clean string
        $code = stri_slug($code, '_');

        // check to see if it already exists
        $already_exists = app('dpo')->select("select code from dpcodes where code = '?' and field_name = '?' and inactive = 'N'", [$code, $field_name])->count() > 0;

        // if it does exist, return it
        if ($already_exists) {
            return $code;
        }

        // try creating the code
        app('dpo')
            ->table('dpcodes')
            ->insert([
                'field_name' => strtoupper($field_name),
                'code' => $code,
                'description' => $description ?? $code,
                'created_by' => sys_get('dpo_user_alias'),
                'created_date' => fromLocalFormat('today', 'Y-m-d'),
                'modified_by' => sys_get('dpo_user_alias'),
                'modified_date' => fromLocalFormat('today', 'Y-m-d'),
            ]);

        // set the code to ACTIVE (whether it existed already or not)
        app('dpo')
            ->table('dpcodes')
            ->where('field_name', '=', $field_name)
            ->where('code', '=', $code)
            ->update([
                'inactive' => 'N',
                'modified_by' => sys_get('dpo_user_alias'),
                'modified_date' => fromLocalFormat('today', 'Y-m-d'),
            ]);

        // clear the verified code cache
        Cache::forget("verified-$field_name");

        return $code;
    }

    /**
     * Get the gift udfs.
     *
     * @return array
     */
    public function getGiftUdfs()
    {
        $udfs = [
            'gift_type',
            'solicit_code',
            'sub_solicit_code',
            'campaign',
            'fmv',
        ];

        foreach (app('dpo')->select('SELECT TOP 1 * FROM dpgiftudf') as $row) {
            $row = get_object_vars($row);
            $udfs = array_merge($udfs, array_keys($row));
        }

        sort($udfs);

        return $udfs;
    }

    /**
     * Get codes.
     *
     * @param string $fieldName
     * @param bool $activeOnly
     * @return array
     */
    public function getCodes($fieldName, $activeOnly = true)
    {
        $codes = collect();

        $query = app('dpo')->table('dpcodes')
            ->select('code', 'description')
            ->where('field_name', $fieldName)
            ->when($activeOnly, function ($query) {
                $query->where('inactive', 'N');
            })->orderBy('code');

        $query->chunk(500, function ($results) use (&$codes) {
            $codes = $codes->merge($results);
        });

        return $codes->sortBy('code')->values()->all();
    }

    public function updateCalculatedField(int $field_id, int $gift_id, ?int $donor_id = null): void
    {
        $this->procedure('dp_recalcFieldDonor', [
            'calc_field_id' => $field_id,
            'record_id' => $gift_id,
            'donor_id' => $donor_id,
        ]);
    }

    public function updateCalculatedFields(int $gift_id, ?int $donor_id = null): void
    {
        if (! sys_get('dp_trigger_calculated_fields')) {
            return;
        }

        $this->getCalculatedFields()->each(function (object $field) use ($gift_id, $donor_id) {
            $this->updateCalculatedField($field->calc_field_id, $gift_id, $donor_id);
        });
    }

    public function getCalculatedFields(): Collection
    {
        $query = app('dpo')->table('dp_calculated_fields')
            ->select([
                'calc_field_id',
                'description',
            ])->where('inactive', 'N')
            ->where('table_to_calculate', 'gift');

        return $query->get();
    }

    /**
     * Apply the dplink scope to the query,
     * assuming the dp table is being queried.
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param string $donor_id_key
     * @return \Illuminate\Database\Query\Builder
     */
    private function scopeByDpLink($query, $donor_id_key = 'dp.donor_id')
    {
        if (sys_get('dp_use_link_scope')) {
            return $query->join('dplink', function ($join) use ($donor_id_key) {
                $join->on('dplink.donor_id', '=', $donor_id_key)
                    ->where('dplink.donor_id2', '=', sys_get('dp_link_donor_id2'))
                    ->where('dplink.link_code', '=', sys_get('dp_link_code'));
            });
        }

        return $query;
    }
}
