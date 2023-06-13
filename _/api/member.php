<?php

use Ds\Models\Email;
use Ds\Models\Member;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

function member($attr = null, $forceRefresh = false)
{
    // check if a member is authenticated
    $authenticated_id = session('member_id');
    if (! $authenticated_id) {
        return;
    }

    $member = reqcache()->get('member');

    // check if member needs to be recached
    if ($forceRefresh || ($member && $member->id != $authenticated_id)) {
        reqcache()->forget(['member', 'member-data']);
    }

    $member = reqcache('member', function () use ($authenticated_id) {
        return \Ds\Models\Member::with('groups.promocodes', 'accountType')->find($authenticated_id);
    });

    // session contains an invalid member
    if (! $member) {
        session()->forget('member_id');

        return;
    }

    if ($attr) {
        // cache serialization member to prevent unnecessary repeated
        // serialization from occurring on subsequent invokations
        $data = reqcache('member-data', function () use ($member) {
            return $member->toObject();
        });

        return $data->{$attr} ?? null;
    }

    return $member;
}

function member_get($id = null, $with_orders = false)
{
    if (! isset($id)) {
        return false;
    }

    $memberModel = Member::with('groups', 'accountType')->findOrFail($id);

    $member = json_decode($memberModel->toJson());

    return ($with_orders) ? member_with_orders($member, $memberModel) : $member;
}

function member_with_orders($member, Member $memberModel)
{
    // retreive all orders
    $member->orders = member_get_all_orders($member->id);

    // total the amounts
    $member->total_order_amount = (float) $memberModel->total_order_amount;

    // average order amount
    $member->average_order_amount = 0;
    if (count($member->orders) > 0) {
        $member->average_order_amount = (float) ($member->total_order_amount / count($member->orders));
    }

    // return the new member object
    return $member;
}

function member_get_value($variable)
{
    // get member
    $m = member();

    // if the member is logged in and param exists
    // USE TO ARRAY TO SAFELY EVALUATE THE EXISTENCE OF MODEL ATTRIBUTES
    // (this prevents people form testing attribtues like 'delete' or 'update' or 'restore')
    if (isset($m, $m->toArray()[$variable])) {
        // auto-format dates
        if (is_object($m->{$variable}) && get_class($m->{$variable}) === 'Carbon\Carbon') {
            return toLocalFormat($m->{$variable}, 'M j, Y');
        }

        return $m->{$variable};
    }

    // otherwise return null
    return null;
}

function member_get_all_orders($member_id)
{
    return DB::select(
        'SELECT o.*,
                IFNULL(i.item_count,0) AS item_count,
                IFNULL(i.total_qty,0) AS total_qty
            FROM productorder o
            LEFT JOIN (SELECT productorderid, COUNT(*) AS item_count, SUM(qty) AS total_qty FROM productorderitem GROUP BY productorderid) i
                ON i.productorderid = o.id
            INNER JOIN member m ON m.id = o.member_id
            WHERE o.member_id = ?
                AND o.confirmationdatetime IS NOT NULL
                AND o.is_processed = 1
                AND o.deleted_at IS NULL
            ORDER BY o.confirmationdatetime DESC',
        [$member_id]
    );
}

function member_login($email = '', $password = '', $remember_me = false)
{
    $member = \Ds\Models\Member::query()
        ->where('email', $email)
        ->where('is_active', true)
        ->whereNotNull('password')
        ->first();

    if (empty($member)) {
        return false;
    }

    // Check for old SHA1-style passwords and auto-upgrade
    if (Str::contains($member->password, '$') === false) {
        if ($member->password !== member_hash_password($password)) {
            return false;
        }

        $member->password = bcrypt($password);
        $member->save();
    }

    if (app('hash')->driver('bcrypt')->check($password, $member->password)) {
        return member_login_with_id($member->id, $remember_me);
    }

    return false;
}

function member_login_with_id($member_id, $remember_me = false)
{
    // store member
    $member = member_get($member_id);

    // refresh the remember token
    if ($remember_me) {
        member_regenerate_member_token($member_id);
    }

    // store session
    DB::table('member_login')
        ->insert([
            'member_id' => (int) $member_id,
            'user_agent' => request()->server('HTTP_USER_AGENT'),
            'ip' => request()->ip(),
            'login_at' => now(),
            'impersonated_by' => user('id') ?? null,
        ]);

    // update profile w/ DP's info
    if (sys_get('allow_account_users_to_update_donor') == '1' && is_numeric($member->donor_id) && $member->donor_id > 0) {
        try {
            member_update_from_dpo($member);

            // refresh member data
            $member = member_get($member_id);
        } catch (Throwable $e) {
            // do nothing
        }
    }

    // quickly update this member with DPO info before we continue
    if ((sys_get('keep_memberships_synced_with_dpo') == '1' && $member->sync_status != 0) || $member->sync_status == 1) {
        try {
            $member = member_update_membership_status_from_dpo($member);
        } catch (Throwable $e) {
            // do nothing
        }
    }
    if ($member === false) {
        $member = member_get($member_id);
    }

    // store in session
    session(['member_id' => $member->id]);

    return $member;
}

function member_remember_token_cookie_name()
{
    return 'remember_' . md5(sys_get('ds_account_name'));
}

function member_login_with_token()
{
    // build the name of the remember-me cookie for this site
    $cookie_name = member_remember_token_cookie_name();

    // if the remember cookie exists
    if (request()->cookie($cookie_name)) {
        // extract the email and remember me token
        [$email, $remember_token] = safe_explode('|', request()->cookie($cookie_name), 2);

        // find the member this token belongs to
        $member_id = db_var("SELECT id FROM `member` WHERE email = '%s' AND remember_token = '%s' AND is_active = 1", $email, $remember_token);

        // if a member is found
        if ($member_id) {
            // log 'em in
            return member_login_with_id($member_id);
        }
    }

    // return fail
    return false;
}

function member_regenerate_member_token($member_id)
{
    $token = password_hash(time(), PASSWORD_DEFAULT);

    // update the member's remember_token (and return the member)
    $member = member_update([
        'id' => $member_id,
        'remember_token' => $token,
    ]);

    // save remember token and auth identifier (email) in cookie
    Cookie::queue(
        member_remember_token_cookie_name(),
        $member->email . '|' . $token,
        now()->addYear()->diffInMinutes()
    );
}

function member_clear_member_token($member_id)
{
    // update the member's remember_token (and return the member)
    $member = member_update([
        'id' => $member_id,
        'remember_token' => '',
    ]);

    // clear the cookie
    Cookie::queue(Cookie::forget(
        member_remember_token_cookie_name()
    ));
}

function member_logout()
{
    // refresh the remember token
    if (member_is_logged_in()) {
        member_clear_member_token(session('member_id'));
    }

    // logout the user
    session()->forget('member_id');

    // if there is an active cart session
    if ($cart = cart()) {
        // remove the member from the cart
        $cart->unpopulateMember();

        // remove all cart promotions
        // this prevents member-based promos from remaining in the cart
        // we're not support concerned about non-member promos here
        // they can re-apply them
        // (its not often someone is going to logout mid-checkout)
        $cart->clearPromos();
    }
}

function member_is_logged_in()
{
    return (bool) session('member_id');
}

function member_hash_password($password = null)
{
    if (! isset($password)) {
        return '';
    }

    return sha1($password . env('APP_LEGACY_KEY'));
}

function member_update_membership_status_from_dpo($member)
{
    // dpo_membership_for_donor($member->donor_id);
    $dpo_membership = dpo_get_membership_for_donor($member->donor_id);
    if (! $dpo_membership) {
        return false;
    }

    // find the matching membership id in our system
    $group = \Ds\Models\Membership::whereDpId($dpo_membership->mcat)->first();
    if (! $group) {
        return false;
    }

    // get the current group assignment that matches this membership
    $groupAccount = \Ds\Models\GroupAccount::whereAccountId($member->id)
        ->where('group_id', $group->id)
        ->orderBy('start_date', 'desc')
        ->first();

    // if the group hasn't been assigned, assign it
    if (! $groupAccount) {
        $groupAccount = new \Ds\Models\GroupAccount;
        $groupAccount->account_id = $member->id;
        $groupAccount->group_id = $group->id;
    }

    $groupAccount->start_date = (trim($dpo_membership->mcat_enroll_date) != '') ? toUtcFormat($dpo_membership->mcat_enroll_date, 'Y-m-d') : $groupAccount->start_date;
    $groupAccount->end_date = (trim($dpo_membership->mcat_expire_date) != '') ? toUtcFormat($dpo_membership->mcat_expire_date, 'Y-m-d') : $groupAccount->end_date;
    if ($groupAccount->end_date && $groupAccount->end_date->year == '1900') {
        $groupAccount->end_date = null;
    }
    $groupAccount->source = 'DP';
    $groupAccount->metadata(['dp_data' => $dpo_membership]);
    $groupAccount->save();

    return $member;
}

function member_update_from_dpo($member)
{
    if (! (is_numeric($member->donor_id) && $member->donor_id > 0)) {
        return false;
    }

    try {
        app('Ds\Services\DonorPerfectService')->updateAccountFromDonor(\Ds\Models\Member::find($member->id));
    } catch (\Exception $e) {
        return false;
    }

    return true;
}

function member_create_from_order($cart_uuid, $password, $auto_login = true)
{
    // get cart
    $cart = cart($cart_uuid);

    // create member
    $member_id = member_sign_up([
        'first_name' => $cart->billing_first_name,
        'last_name' => $cart->billing_last_name,
        'email' => $cart->billingemail,
        'password' => $password,
        'access' => 'member',
    ]);

    // bail if signup failed
    if ($member_id === false) {
        return false;
    }

    // update the member
    $memberModel = \Ds\Models\Member::find($member_id);
    $memberModel->title = $cart->billing_title;
    $memberModel->ship_first_name = $cart->shipping_first_name;
    $memberModel->ship_last_name = $cart->shipping_last_name;
    $memberModel->ship_organization_name = $cart->shipping_organization_name;
    $memberModel->ship_title = $cart->shipping_title;
    $memberModel->ship_email = $cart->shipemail;
    $memberModel->ship_address_01 = $cart->shipaddress1;
    $memberModel->ship_address_02 = $cart->shipaddress2;
    $memberModel->ship_city = $cart->shipcity;
    $memberModel->ship_state = $cart->shipstate;
    $memberModel->ship_zip = $cart->shipzip;
    $memberModel->ship_country = $cart->shipcountry;
    $memberModel->ship_phone = $cart->shipphone;
    $memberModel->bill_title = $cart->billing_title;
    $memberModel->bill_first_name = $cart->billing_first_name;
    $memberModel->bill_last_name = $cart->billing_last_name;
    $memberModel->bill_organization_name = $cart->billing_organization_name;
    $memberModel->bill_email = $cart->billingemail;
    $memberModel->bill_address_01 = $cart->billingaddress1;
    $memberModel->bill_address_02 = $cart->billingaddress2;
    $memberModel->bill_city = $cart->billingcity;
    $memberModel->bill_state = $cart->billingstate;
    $memberModel->bill_zip = $cart->billingzip;
    $memberModel->bill_country = $cart->billingcountry;
    $memberModel->bill_phone = $cart->billingphone;
    $memberModel->donor_id = $cart->alt_contact_id;
    $memberModel->account_type_id = $cart->account_type_id;
    $memberModel->referral_source = $cart->referral_source;
    $memberModel->save();

    // push to dpo
    if (sys_get('dp_auto_sync_orders') == '1') {
        $memberModel->pushToDpo();
    }

    // link that cart with this member
    $cart->member_id = $memberModel->id;
    $cart->save();

    // login the user
    if ($auto_login) {
        member_login_with_id($member_id);
    }

    return true;
}

function member_sign_up($data = [])
{
    // defaults
    $data += [
        'first_name' => '',
        'last_name' => '',
        'email' => '',
        'password' => '',
        'access' => 'member',
    ];

    // does this email exist already?
    if (trim($data['email']) == '') {
        return false;
    }

    if (! member_validate_email(trim($data['email']))) {
        return false;
    }

    // create member
    $model = new Ds\Models\Member;
    $model->first_name = $data['first_name'];
    $model->last_name = $data['last_name'];
    $model->email = $data['email'];
    $model->password = (trim($data['password']) !== '') ? bcrypt($data['password']) : null;
    $model->access = (trim($data['access']) !== '') ? $data['access'] : null;
    $model->save();

    // send welcome email (only if they are a member)
    if (isset($model->password)) {
        event(new \Ds\Events\AccountWasRegistered($model));
    }

    // return member
    return $model->id;
}

function member_sign_up_email_only($data = [])
{
    // defaults
    $data += [
        'first_name' => '',
        'last_name' => '',
        'email' => '',
        'access' => 'member',
    ];

    // does this email exist already?
    if (trim($data['email']) == '') {
        return false;
    }

    if (! member_validate_email(trim($data['email']))) {
        return false;
    }

    // create member
    $member = new Member;
    $member->first_name = $data['first_name'];
    $member->last_name = $data['last_name'];
    $member->email = $data['email'];
    $member->access = $data['access'];
    $member->save();

    // return member
    return $member->id;
}

function member_notify_welcome($member_id)
{
    // get email template
    $email_template = Email::where('type', 'member_welcome')->firstOr(function () {
        return false;
    });

    // bail if inactive
    if (! $email_template || $email_template->is_expired) {
        return false;
    }

    // get member
    $member = \Ds\Models\Member::find($member_id);
    if (! (trim($member->email) !== '' && Swift_Validate::email(trim($member->email)))) {
        return false;
    }

    return $member->notify($email_template->type);
}

function member_notify_updated_profile($member_id, $old_member = null, array $other_updates = [])
{
    $updates = [];

    $new_member = \Ds\Models\Member::findOrFail($member_id);

    // compare old member array with new member data
    // and build a profile difference
    if ($old_member) {
        $attributes = [
            'first_name' => 'First name',
            'last_name' => 'Last name',
            'email' => 'Email',
            'bill_address_01' => 'Billing Address',
            'bill_address_02' => 'Billing Address (Line 2)',
            'bill_city' => 'Billing City',
            'bill_state' => 'Billing ' . sys_get('synonym_province'),
            'bill_zip' => 'Billing Postal Code',
            'bill_phone' => 'Billing Phone',
            'bill_email' => 'Billing Email',
            'ship_address_01' => 'Shipping Address',
            'ship_address_02' => 'Shipping Address (Line 2)',
            'ship_city' => 'Shipping City',
            'ship_state' => 'Shipping ' . sys_get('synonym_province'),
            'ship_zip' => 'Shipping Postal Code',
            'ship_phone' => 'Shipping Phone',
            'ship_email' => 'Shipping Email',
        ];

        foreach ($attributes as $attribute => $label) {
            $new_value = trim($new_member->{$attribute}) ?: '(none)';
            $old_value = trim($old_member->{$attribute}) ?: '(none)';
            if ($new_value !== $old_value) {
                $updates[] = "$label was changed from <em>$old_value</em> to <em>$new_value</em>.";
            }
        }
    }

    $updates = array_merge($updates, $other_updates);

    if (count($updates) > 0) {
        return $new_member->notify('member_profile_update', [
            'profile_updates' => implode('<br />', $updates),
        ]);
    }

    return false;
}

function member_notify_forgot_password($member_id, $temp_password)
{
    // get email template
    $email_template = Email::where('type', 'member_password_reset')->firstOr(function () {
        return false;
    });

    // bail if inactive
    if (! $email_template || $email_template->is_expired) {
        return false;
    }

    // get cart
    $member = \Ds\Models\Member::find($member_id);
    if (! (trim($member->email) !== '' && Swift_Validate::email(trim($member->email)))) {
        return false;
    }

    // params for email
    $params = $member->toArray();
    $params['temporary_password'] = $temp_password;

    if (Str::contains($email_template->body_template, '[[password_reset_link]]')) {
        $params['password_reset_link'] = $member->getAutologinLink('+2 hours', 'account/home');
    }

    return $member->notify($email_template->type, $params);
}

function member_notify_recurring_payment($recurringPaymentProfile, $type = 'success')
{
    return $recurringPaymentProfile->notify("customer_recurring_payment_{$type}");
}

function member_notify_payment_method($paymentMethod, $type = 'expiring')
{
    $template = 'customer_payment_method_' . $type;

    $paymentMethod->member->notify($template, [
        'payment_method' => $paymentMethod->display_name . ' &middot; ' . $paymentMethod->account_number,
    ]);
}

function member_validate_email($email = '')
{
    // make sure the email isn't already in the db
    $members = DB::select('SELECT id FROM `member` WHERE email = ?', [$email]);

    return count($members) == 0;
}

function member_update($data = [])
{
    // no id?  no dice...
    if (! (isset($data['id']) && is_numeric($data['id']))) {
        return false;
    }

    $id = $data['id'];
    unset($data['id']);

    if (isset($data['password'])) {
        $data['password'] = bcrypt($data['password']);
    }

    $member = Ds\Models\Member::find($id);
    $member->fill($data);
    $member->save();

    // return new member
    return member_get($id);
}

function member_verify_access($parent_type, $parent_id)
{
    if ($parent_type == 'node') {
        $node = Ds\Models\Node::find($parent_id);
        if ($node && $node->requires_login == 1 && ! member_is_logged_in()) {
            return false;
        }
        if ($node && $node->isChildOfDonorPortalMenu() && ! member_is_logged_in()) {
            return false;
        }
    }

    // does the current object
    $group_ids = membership_access_get_by_parent($parent_type, $parent_id);

    // if no membership required, smooth sailing ;)
    if (count($group_ids) === 0) {
        return true;
    }

    // if the member is NOT logged in, DENY ACCESS
    if (! member_is_logged_in()) {
        return false;
    }

    // if member's membership_id matches any of the required membership levels, ACCEPT
    return member()->groups->whereIn('pivot.group_id', $group_ids)->count() > 0;
}
