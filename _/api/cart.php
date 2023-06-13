<?php

use Ds\Models\Email;
use Ds\Services\EmailService;
use Illuminate\Support\Arr;

/**
 * Helper to grab the currently active Order model (the 'cart')
 *
 * @return \Ds\Models\Order
 */
function cart($uuid = null)
{
    return \Ds\Models\Order::getActiveSession($uuid);
}

function cart_init($force_init = false)
{
    if ($force_init || ! session()->has('cart_uuid')) {
        $uuid = strtoupper(uuid());

        // find default shipping
        $default_shipping = db_var('SELECT id FROM shipping_method WHERE deleted_at IS NULL AND is_default = 1');

        // default country
        if (trim(sys_get('force_country')) != '') {
            $default_country = sys_get('force_country');
        } elseif (trim(sys_get('default_country')) != '') {
            $default_country = sys_get('default_country');
        } else {
            $default_country = 'US'; // sys default
        }

        $new_order = new \Ds\Models\Order([
            'client_uuid' => $uuid,
            'client_ip' => request()->ip() ?: null,
            'client_browser' => request()->server('HTTP_USER_AGENT') ?: null,
            'http_referer' => request()->server('HTTP_REFERER') ?: null,
            'tracking_source' => request('utm_source') ?: null,
            'tracking_medium' => request('utm_medium') ?: null,
            'tracking_campaign' => request('utm_campaign') ?: null,
            'tracking_term' => request('utm_term') ?: null,
            'tracking_content' => request('utm_content') ?: null,
            'started_at' => fromUtc('now'),
            'currency_code' => sys_get('dpo_currency'),
            'functional_currency_code' => sys_get('dpo_currency'),
            'ship_to_billing' => true,
            'shipping_method_id' => $default_shipping ?: null,
            'billingcountry' => $default_country,
            'shipcountry' => $default_country,
            'dcc_enabled_by_customer' => (bool) sys_get('dcc_ai_is_enabled'),
            'dcc_type' => sys_get('dcc_ai_is_enabled') ? 'more_costs' : null,
            'tax_receipt_type' => sys_get('tax_receipt_type'),
            'dp_sync_order' => (sys_get('dp_auto_sync_orders') == '1'),
            'source' => 'Web',
            'account_type_id' => data_get(\Ds\Models\AccountType::default()->first(), 'id', 1),
        ]);

        $new_order->save();

        session([
            "carts.$uuid" => $uuid,
            'cart_uuid' => $uuid,
        ]);

        // update cart with personal info
        if (member()) {
            $new_order->populateMember(member());
        }
    }

    // has the cart already been processed?  if so, re-init cart
    $is_processed = db_var('SELECT is_processed FROM productorder WHERE client_uuid = %s', session('cart_uuid'));
    if ($is_processed) {
        cart_clear();
        cart_init();
    }

    // return cart uuid
    return session('cart_uuid');
}

function cart_clear()
{
    $uuid = session('cart_uuid');
    if ($uuid) {
        session()->forget("carts.$uuid");
        session()->forget('cart_uuid');
    }
}

function cart_countries()
{
    // This is a temporary fix addressing performance issues related to calling
    // cart_countries repeatedly inside loops inside our templates.
    return reqcache('cart_countries', function () {
        return Arr::pluck(app('iso3166')->countries(), 'name', 'alpha_2');
    });
}

function cart_send_site_owner_email($cart_uuid)
{
    // get email template
    $email_template = Email::where('type', 'admin_order_received')->firstOr(function () {
        return false;
    });

    // bail if inactive
    if ($email_template === false || $email_template->is_expired) {
        return false;
    }

    // get cart
    $cart = cart($cart_uuid);

    // merge codes
    $params = $cart->notifyParams();
    $body = string_substituteFromArray($email_template->body_template, $params);
    $subject = string_substituteFromArray($email_template->subject, $params);

    // collect addresses from global settings & specific products
    $product_email_addresses = cart_collect_email_notify_for_products($cart);

    // prep message params
    $message = (new Swift_Message)
        ->setFrom(sys_get('email_from_address'), sys_get('email_from_name'))
        ->setSubject($subject)
        ->setBody($body, 'text/html');

    // to
    if ($email_template->to !== '') {
        $message->setTo(app(EmailService::class)->getValidEmailsFromString($email_template->to));
    }

    // more to...
    foreach ($product_email_addresses as $email) {
        if (trim($email) !== '' && Swift_Validate::email(trim($email))) {
            $message->addTo(trim($email));
        }
    }

    // cc
    if ($email_template->cc !== '') {
        $message->setCc(app(EmailService::class)->getValidEmailsFromString($email_template->cc));
    }

    // bcc
    if ($email_template->bcc !== '') {
        $message->setBcc(app(EmailService::class)->getValidEmailsFromString($email_template->bcc));
    }

    // now actually send the message
    try {
        $send_status = send_using_swiftmailer($message);
    } catch (Throwable $e) {
        $send_status = false;
        notifyException($e);
    }

    return ($send_status) ? true : false;
}

function cart_collect_email_notify_for_products($cart)
{
    // we need to collect a unique list of email addresses
    // we're collecting all the addresses from product.email_notify (comma delimited)

    // addresss
    $emails = [];

    // loop through each product
    foreach ($cart->items as $item) {
        if (empty($item->variant->product)) {
            continue;
        }

        $addresses = explode(',', $item->variant->product->email_notify);
        // loop throuch each address
        foreach ($addresses as $address) {
            // clean email
            $address = trim($address);

            // make sure email isn't blank
            if ($address === '' || $address === null) {
                continue;
            }

            // if its not already in the list, add it
            if (! in_array($address, $emails)) {
                $emails[] = $address;
            }
        }
    }

    // return array of emails
    return $emails;
}

function cart_send_customer_email($cart_uuid)
{
    // get email template
    $email_template = Email::where('type', 'customer_order_received')->firstOr(function () {
        return false;
    });

    // bail if inactive
    if ($email_template === false || $email_template->is_expired) {
        return false;
    }

    // get cart
    $cart = cart($cart_uuid);

    // bail if email is blank
    if (! (trim($cart->billingemail) !== '' && Swift_Validate::email(trim($cart->billingemail)))) {
        return false;
    }

    // check for products which should not receive the customer email
    $codes = sys_get('list:disable_customer_email_for_products');

    foreach ($cart->items as $item) {
        if (in_array($item->variant->product->code ?? null, $codes, true)) {
            return false;
        }
    }

    // Look for custom emails that would short-circuit this notification
    $disablesGenericEmail = false;

    $cart->items->each(function (Ds\Models\OrderItem $item) use (&$disablesGenericEmail) {
        if (! $item->variant) {
            return;
        }
        if ($item->variant->emails()->active()->where('disables_generic', true)->get()->isNotEmpty()) {
            $disablesGenericEmail = true;
        }
        if ($item->variant->product->emails()->active()->where('disables_generic', true)->get()->isNotEmpty()) {
            $disablesGenericEmail = true;
        }
    });

    if ($disablesGenericEmail) {
        return false;
    }

    // merge codes
    $params = $cart->notifyParams();
    $body = string_substituteFromArray($email_template->body_template, $params);
    $subject = string_substituteFromArray($email_template->subject, $params);

    // prep message params
    $message = (new Swift_Message)
        ->setFrom(sys_get('email_from_address'), sys_get('email_from_name'))
        ->addTo($cart->billingemail, $cart->billing_first_name . ' ' . $cart->billing_last_name)
        ->setSubject($subject)
        ->setBody($body, 'text/html');

    // cc
    if ($email_template->cc !== '') {
        $message->setCc(app(EmailService::class)->getValidEmailsFromString($email_template->cc));
    }

    // bcc
    if ($email_template->bcc !== '') {
        $message->setBcc(app(EmailService::class)->getValidEmailsFromString($email_template->bcc));
    }

    // now actually send the message
    try {
        $send_status = send_using_swiftmailer($message);
    } catch (Throwable $e) {
        $send_status = false;
        notifyException($e);
    }

    return ($send_status) ? true : false;
}

function cart_get_downloads($cart_uuid)
{
    // query db
    $qDownloads = db_query(sprintf(
        "
            SELECT f.id AS file_id,
                (CASE WHEN IFNULL(iv.variantname,'') = '' THEN p.name ELSE iv.variantname END) AS product_variant_name,
                `of`.external_resource_uri,
                `of`.id AS order_item_file_id
            FROM productorderitemfiles `of`
            LEFT JOIN files f ON f.id = `of`.fileid
            INNER JOIN productorderitem i ON i.id = `of`.orderitemid
            INNER JOIN productinventory iv ON iv.id = i.productinventoryid
            INNER JOIN productorder o ON o.id = i.productorderid
            INNER JOIN product p ON p.id = iv.productid
            WHERE o.invoicenumber = '%s'",
        db_real_escape_string($cart_uuid)
    ));

    if ($qDownloads === false || db_num_rows($qDownloads) === 0) {
        return false;
    }

    $downloads = [];

    while ($download = db_fetch_object($qDownloads)) {
        // encrypt purchase id
        $url_order_file_id = app('hashids')->encode($download->order_item_file_id);

        // set the url
        $download->url = secure_site_url("ds/file?o={$url_order_file_id}");

        // add record
        $downloads[] = $download;
    }

    return $downloads;
}

function cart_send_downloads($cart_uuid)
{
    // get downlaods
    $downloads = cart_get_downloads($cart_uuid);
    if ($downloads === false) {
        return false;
    }

    // get email template
    $email_template = Email::where('type', 'customer_downloads')->firstOr(function () {
        return false;
    });

    // bail if inactive
    if ($email_template === false || $email_template->is_expired) {
        return false;
    }

    // get cart
    $cart = cart($cart_uuid);

    // bail if email is blank
    if (! (trim($cart->billingemail) !== '' && Swift_Validate::email(trim($cart->billingemail)))) {
        return false;
    }

    // merge codes
    $params = $cart->notifyParams();

    // download links
    $downloads_html = [];
    foreach ($downloads as $download) {
        // if its a file from our system, send the download link
        if (is_int($download->file_id)) {
            $downloads_html[] = $download->product_variant_name . ' - Download: <a href="' . $download->url . '" target="_blank">' . $download->url . '</a>';
        } else {
            // if its embeddable, send them to the page to view the item
            if (oembed_get($download->external_resource_uri)) {
                $url = secure_site_url("account/purchased-media/{$download->order_item_file_id}");
                $downloads_html[] = $download->product_variant_name . ' - View: <a href="' . $url . '" target="_blank">' . $url . '</a>';
            // if its a link to a non-embeddable resource, link to the resource directly
            } else {
                $downloads_html[] = $download->product_variant_name . ' - Download: <a href="' . $download->external_resource_uri . '" target="_blank">' . $download->external_resource_uri . '</a>';
            }
        }
    }
    $params['download_links'] = implode('<br />', $downloads_html);

    // merge
    $body = string_substituteFromArray($email_template->body_template, $params);
    $subject = string_substituteFromArray($email_template->subject, $params);

    // prep message params
    $message = (new Swift_Message)
        ->setFrom(sys_get('email_from_address'), sys_get('email_from_name'))
        ->addTo($cart->billingemail, $cart->billing_first_name . ' ' . $cart->billing_last_name)
        ->setSubject($subject)
        ->setBody($body, 'text/html');

    // cc
    if ($email_template->cc !== '') {
        $message->setCc(app(EmailService::class)->getValidEmailsFromString($email_template->cc));
    }

    // bcc
    if ($email_template->bcc !== '') {
        $message->setBcc(app(EmailService::class)->getValidEmailsFromString($email_template->bcc));
    }

    // now actually send the message
    try {
        $send_status = send_using_swiftmailer($message);
    } catch (Throwable $e) {
        $send_status = false;
        notifyException($e);
    }

    return ($send_status) ? true : false;
}
