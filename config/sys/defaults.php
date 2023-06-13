<?php

return [
    'active_theme' => 3,
    'account_login_features' => 'view-profile,view-billing,view-shipping,edit-profile,edit-billing,edit-shipping,view-orders,view-receipts,view-payment-methods,view-purchased-media,view-giving-impact,edit-payment-methods,view-sponsorships,end-sponsorships,edit-sponsorship-amount,edit-sponsorship-date,edit-sponsorship-frequency,view-subscriptions,end-subscriptions,edit-subscriptions-amount,edit-subscriptions-date,edit-subscriptions-frequency,view-memberships',
    'dpo_user' => '',
    'dpo_pass' => '',
    'dpo_api_key' => '',
    'dpo_currency' => 'USD',
    'dpo_partner_tag' => 'DPPGCLOUD',
    'locale' => '',
    'local_currencies' => '',
    'money_with_currency_preference' => '0',
    'use_givecloud_express' => '0',
    'dcc_enabled' => 0,
    'dcc_ai_is_enabled' => 1,
    'dcc_cost_per_order' => '0.30',
    'dcc_percentage' => '2.89',
    'dcc_stripe_application_fee_billing' => 0,
    'dcc_checkout_label' => 'trans:defaults.cover_the_fees',
    'dcc_checkout_description' => 'trans:defaults.cover_the_fees_for_most',
    'dcc_checkout_description_with_amount' => 'trans:defaults.cover_the_fees_for_amount',
    'dcc_label' => 'DCC',
    'dcc_invoice_label' => 'trans:defaults.admin_fees',
    'dcc_enabled_on_sponsorships' => 1,
    'ss_dup_seconds' => '',
    'ss_auth_attempts' => '3',
    'ss_auth_max_attempts' => '6',
    'enforce_ip_blocklists' => '0',
    'public_payments_disabled' => '0',
    'public_payments_disabled_until' => '',
    'require_ip_country_match' => '0',
    'captcha_type' => 'recaptcha',
    'arm_rate_threshold' => '0.65',
    'arm_evaluation_window' => '15',
    'arm_attempt_threshold' => '10',
    'arm_immediate_action' => 'always_require_captcha',
    'arm_recipients' => '',
    'arm_renotify_recipients' => '0',
    'platform_fee_types' => '',
    'thumbnail_size' => '300x?',
    'thumbnail_crop' => 'entropy',
    'clientName' => '',
    'clientShortName' => '',
    'clientDomain' => '',
    'ds_account_name' => '',
    'custom_domain_migration_mode' => '0',
    'defaultPageTitle' => '',
    'forceSSL' => '1',
    'moduleECommerce' => '1',
    'ecomm_syn_author' => 'Filter',
    'webStatsPropertyId' => '',
    'fbAdminID' => '',
    'onboarding_flow' => '0',
    'enable_admin_logrocket' => '1',
    'log_queries_for_ip' => '',
    'synonym_province' => 'trans:defaults.state',
    'show_clearance' => '0',
    'use_category_images' => '0',
    'product_as_homepage' => '',
    'cardtypes' => 'm,v,a,d',
    'cart_synonym' => 'trans:defaults.cart',
    'preserve_amount_on_variant_change' => '1',
    'packing_slip_corporate_header' => 'trans:defaults.packing_slip_corporate_header',
    'packing_slip_contribution_syn' => 'trans:defaults.order',
    'min_days_before_recurring_start_date' => '10',
    'keep_memberships_synced_with_dpo' => '1',
    'allow_account_users_to_update_donor' => '1',
    'allow_signup_in_checkout' => '1',
    'admin_created_accounts_pushed_to_dpo' => '1',
    'bank_account_provider' => '',
    'credit_card_provider' => '',
    'kiosk_provider' => '',
    'wallet_pay_provider' => '',
    'external_donations_start_date' => '',
    'external_donations_end_date' => '',
    'external_donations_gl_codes' => '',
    'external_donations_gift_types' => '',
    'force_recurring_payments_to_extend_memberships' => 0,
    'node_revisions_to_keep' => '5',

    // Org settings
    'org_logo' => '',
    'org_website' => '',
    'org_primary_color' => '#2467CC',
    'org_support_phone' => '',
    'org_support_email' => '',
    'org_faq_alternative_question' => '',
    'org_faq_alternative_answer' => '',
    'org_faq_link_0_label' => '',
    'org_faq_link_0_link' => '',
    'org_faq_link_1_label' => '',
    'org_faq_link_1_link' => '',
    'org_faq_link_2_label' => '',
    'org_faq_link_2_link' => '',
    'org_faq_link_3_label' => '',
    'org_faq_link_3_link' => '',
    'org_faq_link_4_label' => '',
    'org_faq_link_4_link' => '',
    'org_faq_link_5_label' => '',
    'org_faq_link_5_link' => '',
    'org_legal_address' => '',
    'org_check_mailing_address' => '',
    'org_legal_country' => '',
    'org_legal_number' => '',
    'org_privacy_officer_email' => '',
    'org_privacy_policy_url' => '',
    'org_multilingual_support' => '0',
    'org_multilingual_languages' => '',

    // syns
    'syn_order' => 'trans:defaults.order',
    'syn_orders' => 'trans:defaults.orders',
    'syn_cart_heading' => '',
    'syn_cart_view_label' => '',
    'syn_cart_checkout_label' => 'trans:defaults.checkout',
    'syn_checkout_my_cart_label' => '',
    'syn_checkout_checkout_label' => 'trans:defaults.checkout',
    'syn_checkout_billing_label' => 'trans:defaults.billing_information',
    'syn_checkout_shipping_label' => 'trans:defaults.shipping_information',
    'syn_checkout_complete' => 'trans:defaults.complete_purchase',
    'syn_sponsorship_child' => 'trans:defaults.child',
    'syn_sponsorship_children' => 'trans:defaults.children',
    'syn_group' => 'trans:defaults.membership',
    'syn_groups' => 'trans:defaults.memberships',
    'syn_group_member' => 'trans:defaults.member',
    'syn_group_members' => 'trans:defaults.members',

    // dp integration
    'dp_meta9_field' => '',
    'dp_meta9_label' => '',
    'dp_meta9_type' => '',
    'dp_meta9_default' => '',
    'dp_meta9_autocomplete' => '0',
    'dp_meta10_field' => '',
    'dp_meta10_label' => '',
    'dp_meta10_type' => '',
    'dp_meta10_default' => '',
    'dp_meta10_autocomplete' => '0',
    'dp_meta11_field' => '',
    'dp_meta11_label' => '',
    'dp_meta11_type' => '',
    'dp_meta11_default' => '',
    'dp_meta11_autocomplete' => '0',
    'dp_meta12_field' => '',
    'dp_meta12_label' => '',
    'dp_meta12_type' => '',
    'dp_meta12_default' => '',
    'dp_meta12_autocomplete' => '0',
    'dp_meta13_field' => '',
    'dp_meta13_label' => '',
    'dp_meta13_type' => '',
    'dp_meta13_default' => '',
    'dp_meta13_autocomplete' => '0',
    'dp_meta14_field' => '',
    'dp_meta14_label' => '',
    'dp_meta14_type' => '',
    'dp_meta14_default' => '',
    'dp_meta14_autocomplete' => '0',

    'dp_meta15_field' => '',
    'dp_meta15_label' => '',
    'dp_meta15_type' => '',
    'dp_meta15_default' => '',
    'dp_meta15_autocomplete' => '0',
    'dp_meta16_field' => '',
    'dp_meta16_label' => '',
    'dp_meta16_type' => '',
    'dp_meta16_default' => '',
    'dp_meta16_autocomplete' => '0',
    'dp_meta17_field' => '',
    'dp_meta17_label' => '',
    'dp_meta17_type' => '',
    'dp_meta17_default' => '',
    'dp_meta17_autocomplete' => '0',
    'dp_meta18_field' => '',
    'dp_meta18_label' => '',
    'dp_meta18_type' => '',
    'dp_meta18_default' => '',
    'dp_meta18_autocomplete' => '0',
    'dp_meta19_field' => '',
    'dp_meta19_label' => '',
    'dp_meta19_type' => '',
    'dp_meta19_default' => '',
    'dp_meta19_autocomplete' => '0',
    'dp_meta20_field' => '',
    'dp_meta20_label' => '',
    'dp_meta20_type' => '',
    'dp_meta20_default' => '',
    'dp_meta20_autocomplete' => '0',
    'dp_meta21_field' => '',
    'dp_meta21_label' => '',
    'dp_meta21_type' => '',
    'dp_meta21_default' => '',
    'dp_meta21_autocomplete' => '0',
    'dp_meta22_field' => '',
    'dp_meta22_label' => '',
    'dp_meta22_type' => '',
    'dp_meta22_default' => '',
    'dp_meta22_autocomplete' => '0',
    'dp_meta23_field' => '',
    'dp_meta23_label' => '',
    'dp_meta23_type' => '',
    'dp_meta23_default' => '',
    'dp_meta23_autocomplete' => '0',

    // dp shipping coding
    'dp_shipping_gl' => '',
    'dp_shipping_campaign' => '',
    'dp_shipping_solicit' => '',
    'dp_shipping_subsolicit' => '',
    'dp_shipping_gift_type' => '',
    'dp_shipping_ty_letter_code' => '',
    'dp_shipping_fair_mkt_val' => '',
    'dp_shipping_gift_memo' => 'trans:defaults.shipping_charges',
    'dp_shipping_no_calc' => '',
    'dp_shipping_acknowledgepref' => '',
    'dp_shipping_meta9_value' => '',
    'dp_shipping_meta10_value' => '',
    'dp_shipping_meta11_value' => '',
    'dp_shipping_meta12_value' => '',
    'dp_shipping_meta13_value' => '',
    'dp_shipping_meta14_value' => '',
    'dp_shipping_meta15_value' => '',
    'dp_shipping_meta16_value' => '',
    'dp_shipping_meta17_value' => '',
    'dp_shipping_meta18_value' => '',
    'dp_shipping_meta19_value' => '',
    'dp_shipping_meta20_value' => '',
    'dp_shipping_meta21_value' => '',
    'dp_shipping_meta22_value' => '',

    // dp dcc coding
    'dp_dcc_is_separate_gift' => '1',
    'dp_dcc_gl' => '',
    'dp_dcc_campaign' => '',
    'dp_dcc_solicit' => '',
    'dp_dcc_subsolicit' => '',
    'dp_dcc_gift_type' => '',
    'dp_dcc_ty_letter_code' => '',
    'dp_dcc_fair_mkt_val' => '',
    'dp_dcc_gift_memo' => 'trans:defaults.donor_covers_costs',
    'dp_dcc_no_calc' => '',
    'dp_dcc_acknowledgepref' => '',
    'dp_dcc_meta9_value' => '',
    'dp_dcc_meta10_value' => '',
    'dp_dcc_meta11_value' => '',
    'dp_dcc_meta12_value' => '',
    'dp_dcc_meta13_value' => '',
    'dp_dcc_meta14_value' => '',
    'dp_dcc_meta15_value' => '',
    'dp_dcc_meta16_value' => '',
    'dp_dcc_meta17_value' => '',
    'dp_dcc_meta18_value' => '',
    'dp_dcc_meta19_value' => '',
    'dp_dcc_meta20_value' => '',
    'dp_dcc_meta21_value' => '',
    'dp_dcc_meta22_value' => '',

    // dp taxes coding
    'dp_tax_gl' => '',
    'dp_tax_campaign' => '',
    'dp_tax_solicit' => '',
    'dp_tax_subsolicit' => '',
    'dp_tax_gift_type' => '',
    'dp_tax_ty_letter_code' => '',
    'dp_tax_fair_mkt_val' => '',
    'dp_tax_gift_memo' => 'trans:defaults.taxes',
    'dp_tax_no_calc' => '',
    'dp_tax_acknowledgepref' => '',
    'dp_tax_meta9_value' => '',
    'dp_tax_meta10_value' => '',
    'dp_tax_meta11_value' => '',
    'dp_tax_meta12_value' => '',
    'dp_tax_meta13_value' => '',
    'dp_tax_meta14_value' => '',
    'dp_tax_meta15_value' => '',
    'dp_tax_meta16_value' => '',
    'dp_tax_meta17_value' => '',
    'dp_tax_meta18_value' => '',
    'dp_tax_meta19_value' => '',
    'dp_tax_meta20_value' => '',
    'dp_tax_meta21_value' => '',
    'dp_tax_meta22_value' => '',

    'dp_anonymous_donor_id' => '',
    'dp_push_mcat_enroll_date' => '0',

    // dp auto-sync orders
    'dp_push_txn_refunds' => '0',
    'dp_push_order_refunds' => '0',
    'dp_auto_sync_orders' => '1',
    'dp_auto_sync_txns' => '1',
    'dp_default_rcpt_pref' => 'L',
    'dp_default_rcpt_type' => '',
    'dp_enable_ty_date' => '',
    'dp_match_donor_spouse' => '0',
    'dp_order_comments_to_narrative' => '',
    'dp_tribute_details_to_narrative' => '0',
    'dp_tribute_message_to_narrative' => '1',
    'dp_meta_payment_method' => '',
    'dp_meta_is_rpp' => '',
    'dp_meta_referral_source' => '',
    'dp_meta_item_qty' => '',
    'dp_meta_item_description' => '',
    'dp_meta_item_name' => '',
    'dp_meta_item_variant_name' => '',
    'dp_meta_item_code' => '',
    'dp_meta_item_fmv' => '',
    'dp_meta_order_number' => '',
    'dp_meta_order_source' => '',
    'dp_meta_special_notes' => '',
    'dp_meta_payment_method_default' => '',
    'dp_meta_donor_name' => '',
    'dp_meta_tracking_source' => '',
    'dp_meta_tracking_medium' => '',
    'dp_meta_tracking_campaign' => '',
    'dp_meta_tracking_term' => '',
    'dp_meta_tracking_content' => '',
    'dp_meta_tribute_name' => '',
    'dp_meta_tribute_type' => '',
    'dp_meta_tribute_notify_name' => '',
    'dp_meta_tribute_notify_email' => '',
    'dp_meta_tribute_notify_address' => '',
    'dp_meta_tribute_notify_type' => '',
    'dp_meta_tribute_personal_message' => '',
    'dp_sync_noemail' => '0',
    'dp_sync_salutation' => '1',
    'dp_phone_mapping' => 'home_phone',
    'dp_logging' => '0',

    // p2p settings
    'dp_p2p_soft_credits' => '0',
    'dp_p2p_url_field' => '',

    // multi-tenant support for DP
    'dp_use_link_scope' => '0',
    'dp_link_donor_id2' => '',
    'dp_link_code' => 'client_id',

    // dp coding overrides
    'dp_product_codes_override' => '1',

    // dp split gift use
    'dp_use_split_gifts' => '0',
    'dp_enable_split_gifts' => '',

    // Trigger Calculated fields
    'dp_trigger_calculated_fields' => '1',

    // the user stamp saved on created_by and updated_by columns in DP
    'dpo_user_alias' => 'DonorShops.com',

    'infusionsoft_account' => '',
    'infusionsoft_token' => '',
    'infusionsoft_default_optin_reason' => '',
    'infusionsoft_optin_tag' => '',

    // tax receipts
    'tax_receipt_pdfs' => '0',
    'tax_receipt_type' => 'single',
    'tax_receipt_template' => '<div class="page" title="Page 1">
<div class="section">
<div class="layoutArea">
<div class="column">
<table style="width: 609px; margin-left: auto; margin-right: auto;">
<tbody>
<tr>
<td style="width: 309px;"><span style="font-family: arial, helvetica, sans-serif;"><img style="float: right;" src="https://815393a849b74051d552-f0e6c8ff8d0647d5bbdb36d26d405888.ssl.cf2.rackcdn.com/newtheme/images/your-logo.png" alt="" width="236" height="95" /></span></td>
<td style="width: 299px;">
<p style="text-align: left;"><span style="text-decoration: underline; font-size: 12pt; font-family: arial, helvetica, sans-serif;"><strong>DONATION RECEIPT</strong></span></p>
<div class="page" title="Page 1">
<div class="section">
<div class="layoutArea">
<div class="column">
<p style="text-align: left;"><span style="font-size: 12pt; font-family: arial, helvetica, sans-serif;">555 Test Address<br />Test City, Test State, CA 55555</span></p>
</div>
</div>
</div>
</div>
</td>
</tr>
</tbody>
</table>
<p style="text-align: center;"><span style="font-family: arial, helvetica, sans-serif;"><strong><span style="font-size: 12pt;">Receipt Number:&nbsp;[[number]]</span></strong></span><br /><span style="font-size: 12pt; font-family: arial, helvetica, sans-serif;">Receiptable Amount:&nbsp;[[amount]]</span><br /><span style="font-size: 12pt; font-family: arial, helvetica, sans-serif;">Receipt Date:&nbsp;[[issued_at]]</span></p>
<p><span style="font-size: 12pt; font-family: arial, helvetica, sans-serif;"></span></p>
<p><span style="font-size: 12pt; font-family: arial, helvetica, sans-serif;">Dear <strong>[[name]]</strong>,</span></p>
</div>
</div>
<div class="layoutArea">
<div class="column">
<p><span style="font-size: 12pt; font-family: arial, helvetica, sans-serif;">This is a receipt [[number]] for your donation to XXXXXXXXXXX&nbsp;for &nbsp;$[[amount]].</span></p>
<p><span style="font-size: 12pt; font-family: arial, helvetica, sans-serif;">It is a tax-deductible donation(s) to XXXXXXXXXXX&nbsp;(EIN XXXXXXXXXX).</span></p>
<p><span style="font-size: 12pt; font-family: arial, helvetica, sans-serif;">If you have any questions or concerns, please email XXXXXXXXXX.</span></p>
<p><span style="font-size: 12pt; font-family: arial, helvetica, sans-serif;">We sincerely thank you for your generosity and look forward to continuing our service with you.</span></p>
<p><span style="font-size: 12pt; font-family: arial, helvetica, sans-serif;"></span></p>
<p><span style="font-family: arial, helvetica, sans-serif;"><span style="font-size: 12pt;">YOUR RECEIPT*</span><br /><span style="font-size: 12pt;">Keep this for your tax records</span><br /></span></p>
<p><span style="font-family: arial, helvetica, sans-serif;"></span></p>
<p style="text-align: center;"><span style="font-size: 10pt; font-family: arial, helvetica, sans-serif;"><em>*Please Note: This receipt is for your donation made to XXXXXXXXX. No goods or services were provided in exchange for your contribution. XXXXXXXXXX&nbsp;is a 501(c)(3) non-profit organization. Federal Tax ID #XXXXXXXXX. We confirm that XXXXXXXXXXXXXX is eligible to receive tax-deductible contributions in accordance with Internal Revenue Code Section 170.</em></span></p>
<p style="text-align: center;"><span style="font-size: 10pt; font-family: arial, helvetica, sans-serif;"><em>&nbsp;</em></span></p>
<p style="text-align: left;"><span style="font-size: 10pt; font-family: arial, helvetica, sans-serif;">[[changes]]</span></p>
</div>
</div>
</div>
</div>',
    'tax_receipt_country' => 'US',
    'tax_receipt_number_format' => 'WEB[YY]-[000000000]',
    'tax_receipt_summary_include_description' => '1',
    'tax_receipt_summary_include_gl' => '0',

    // recurring payment profiles
    'rpp_donorperfect' => '0',
    'rpp_require_login' => '1',
    'rpp_default_type' => 'fixed',
    'rpp_process_at' => '0500',
    'rpp_retry_attempts' => '1',
    'rpp_retry_interval' => '7',
    'rpp_auto_bill_out_amt' => '0',
    'rpp_default_initial_charge' => '',
    'rpp_nsf_fee' => '',
    'rpp_start_date_snap_Week' => 'organization',
    'rpp_start_date_snap_SemiMonth' => 'organization',
    'rpp_start_date_snap_Month' => 'organization',
    'rpp_start_date_snap_Quarter' => 'donor',
    'rpp_start_date_snap_SemiYear' => 'donor',
    'rpp_start_date_snap_Year' => 'donor',
    'payment_day_of_week_options' => '1,2,3,4,5,6,7',
    'payment_day_options' => '1,15',
    'rpp_cancel_reasons' => 'trans:defaults.cancel_reasons',
    'rpp_cancel_allow_other_reason' => '0',
    'rpp_legacy_profiles' => '0',

    'account_updater_reactivate_suspended' => '0',
    'account_updater_max_last_payment_days_ago' => '30',

    // feature related
    'feature_accounts' => '1',
    'feature_add_from_vault' => '0',
    'feature_buckets' => '1',
    'feature_check_ins' => '1',
    'feature_customfields' => '0',
    'feature_ecomm_wallet_pay' => '0',
    'feature_edit_order_items' => '0',
    'feature_allow_previewing_new_ui' => '0',
    'feature_edownloads' => '1',
    'feature_fundraising_pages' => '0',
    'feature_kiosks' => '0',
    'feature_legacy_importer' => '0',
    'feature_linked_products' => '0',
    'feature_membership' => '1',
    'feature_messenger' => '1',
    'feature_metadata' => '0',
    'feature_onepage' => '1',
    'feature_pledges' => '0',
    'feature_promos' => '1',
    'feature_shipping' => '1',
    'feature_sites' => '0',
    'feature_social' => '1',
    'feature_sponsorship' => '1',
    'feature_stock' => '1',
    'feature_tax_receipt' => '1',
    'feature_taxes' => '1',
    'feature_trackorder' => '1',
    'feature_virtual_events' => '1',
    'feature_virtual_events_mux_streaming' => '1',
    'feature_embedded_donation_forms' => '1',
    'feature_account_notes' => '1',
    'feature_imports' => '0',
    'feature_supporter_search' => '1',
    'feature_fundraising_forms' => '1',
    'feature_fundraising_forms_peer_to_peer' => '0',
    'feature_fundraising_forms_standard_layout' => '1',
    'feature_social_login' => '1',
    'feature_dcc_ai_plus' => '1',
    'feature_flatfile_contributions_imports' => '1',
    'feature_flatfile_supporter_imports' => '1',
    'feature_flatfile_sponsorships_imports' => '1',
    'feature_double_the_donation' => '1',
    'feature_unified_contributions_listing' => '0',

    // shipping related
    'shipping_expectation_threshold' => '',
    'shipping_expectation_over' => 'trans:defaults.usually_ships_in_2_3_days',
    'shipping_expectation_under' => 'trans:defaults.usually_ships_in_2_3_weeks',
    'shipping_taxes_apply' => '0',
    'shipping_handler' => 'tiered',
    'shipping_linked_items' => 'both',

    'shipping_canadapost_enabled' => '0',
    'shipping_canadapost_customer_number' => '0008098595',
    'shipping_canadapost_user' => 'b468d0e1c86c2abe',
    'shipping_canadapost_pass' => '58133650f58695e59ea37e',

    'shipping_ups_enabled' => '0',
    'shipping_ups_access_key' => '6D04A8E0105D1684',
    'shipping_ups_user' => 'donorshops',
    'shipping_ups_pass' => 'dnrshp8!',
    'shipping_ups_account' => '',
    'shipping_ups_servicecodes' => '',
    'shipping_ups_negotiated_rates' => '0',

    'shipping_fedex_enabled' => '0',
    'shipping_fedex_key' => 'xKEyVY9Tf9JR4O4x',
    'shipping_fedex_pass' => 'Muxsaa5HU8lfqlR95wPXKJhg2',
    'shipping_fedex_account' => '904377813',
    'shipping_fedex_meter' => '111919079',
    'shipping_fedex_net_discount' => '0',
    'shipping_fedex_servicecodes' => '',

    'shipping_usps_enabled' => '0',
    'shipping_usps_user' => '568DONOR5227',
    'shipping_usps_pass' => '535IT13HC737',
    'shipping_usps_classids' => '3,1,4,6,7',
    'shipping_usps_interids' => '12,1,26,2,11,9,16,24,25,15',

    'shipping_from_state' => 'ON',
    'shipping_from_zip' => 'K0A2Z0',
    'shipping_from_country' => 'CA',

    'email_from_name' => 'Givecloud',
    'email_from_address' => 'notifications@givecloud.co',
    'email_replyto_address' => '',
    'email_sender_required' => '1',

    'login_success_contingency_url' => '/account/home',

    'sponsorship_maturity_age' => '18',
    'sponsorship_max_sponsors' => '1',
    'sponsorship_num_sponsors' => '1',
    'sponsorship_sources' => 'Mail,Phone,Website,Special Event,Other',
    'sponsorship_end_reasons' => 'trans:defaults.sponsorship_end_reasons',
    'sponsorship_show_sponsored_on_details_page' => '',
    'sponsorship_show_sponsored_on_web' => '1',
    'sponsorship_default_sorting' => 'id_asc',
    'sponsorship_database_name' => '',
    'sponsorship_tax_receipts' => '0',
    'sponsorship_end_on_rpp_suspend' => '0',
    'sponsorship_end_on_rpp_cancel' => '1',
    'allow_member_to_end_sponsorship' => '0',
    'public_sponsorship_end_reasons' => 'trans:defaults.public_sponsorship_end_reasons',

    'checkout_min_value' => '5',

    'dp_use_nocalc' => '1',

    'fbAppId' => '',

    'sales_channel' => 'softerware',
    'intercom_end_date' => '',
    'site_password' => '',
    'site_password_message' => '',
    'is_suspended' => '0',

    'category_default_order_by' => 'publish_start_date DESC',

    'pos_sources' => 'Phone,Mail,Fax,Web',
    'pos_tax_address1' => '',
    'pos_tax_address2' => '',
    'pos_tax_city' => '',
    'pos_tax_state' => '',
    'pos_tax_zip' => '',
    'pos_tax_country' => '',
    'pos_allow_expired_products' => '0',
    'pos_use_default_tax_region' => '0',

    'timezone' => 'America/New_York',
    'timezone_confirmed' => '0',

    'web_allow_indexing' => '1',

    'taxcloud_api_key' => '',
    'taxcloud_api_login_id' => '',
    'taxcloud_origin_address1' => '',
    'taxcloud_origin_address2' => '',
    'taxcloud_origin_city' => '',
    'taxcloud_origin_state' => '',
    'taxcloud_origin_zip' => '',

    'twilio_subaccount_sid' => '',
    'twilio_subaccount_token' => '',

    'shipstation_api_key' => '',
    'shipstation_api_secret' => '',
    'shipstation_user' => '',
    'shipstation_pass' => '',

    'referral_sources_isactive' => '0',
    'referral_sources_options' => 'Facebook,Instagram,Website,Friend/Family,Event',
    'referral_sources_other' => '0',

    'nps_enabled' => '0',

    'donor_title' => 'hidden',
    'donor_title_options' => 'trans:defaults.donor_title_options',

    'marketing_optout_reason_required' => '1',
    'marketing_optout_options' => 'trans:defaults.marketing_optout_options',
    'marketing_optout_other' => '1',

    'allow_account_types_on_web' => '1',

    'fundraising_forms_initial_edit_at' => '',

    'fundraise_early_access_requested' => '0',

    'fundraising_pages_enabled' => '0',
    'fundraising_pages_requires_verify' => '0',
    'fundraising_pages_did_verify_former_pages' => '0',
    'fundraising_pages_auto_verifies' => '0',
    'fundraising_page_pending_message' => 'This page is not yet viewable by others. We are in the process of reviewing this page and will notify you if it is approved.',
    'fundraising_page_denied_message' => 'This page is not viewable by others. It did not pass our approval process. Please contact us if you have any questions.',

    'fundraising_pages_report_reasons' => 'trans:defaults.fundraising_pages_report_reasons',
    'fundraising_pages_require_guideline_acceptance' => '0',
    'fundraising_pages_profanity_filter' => '1',
    'fundraising_pages_categories' => 'trans:defaults.fundraising_pages_categories',
    'fundraising_pages_guidelines' => 'trans:defaults.fundraising_pages_guidelines',

    // Mailing Lables
    'ml_enabled' => '0',
    'ml_page_top_margin' => '0.5',
    'ml_page_left_margin' => '0.5',
    'ml_page_column_spacing' => '0.05',
    'ml_page_row_spacing' => '0',
    'ml_page_label_count' => '30',
    'ml_label_inner_margin' => '0.09',
    'ml_label_width' => '2',
    'ml_label_height' => '0.5',
    'ml_label_font_size' => '9',
    'ml_label_template' => '<p>[[name_formatted]]<br />[[address_formatted]]</p>',

    'default_logo' => '',
    'default_color_1' => '',
    'default_color_2' => '',
    'default_color_3' => '',

    // fee charged on each transaction
    'transaction_fee_rate' => '0.0125',

    // gift aid support
    'gift_aid' => '0',

    // country settings
    'pinned_countries' => 'trans:defaults.pinned_countries',
    'default_country' => 'trans:defaults.default_country',
    'force_country' => '',

    'zapier_enabled' => '0',

    // Salesforce
    'feature_salesforce' => '0',
    'salesforce_enabled' => '0',
    'salesforce_consumer_key' => '',
    'salesforce_consumer_secret' => '',

    // Double the donation
    'double_the_donation_enabled' => '0',
    'double_the_donation_public_key' => '',
    'double_the_donation_private_key' => '',
    'double_the_donation_sync_all_contributions' => '1',

    // HotGlue
    'feature_hotglue_salesforce' => '1',
    'hotglue_salesforce_linked' => '0',
    'salesforce_contact_external_id' => '',
    'salesforce_opportunity_external_id' => '',
    'salesforce_recurring_donation_external_id' => '',

    'feature_hotglue_mailchimp' => '1',
    'hotglue_mailchimp_linked' => '0',

    'feature_hotglue_hubspot' => '0',
    'hotglue_hubspot_linked' => '0',

    /* ================== */
    /* ================== */
    /* same for all sites */
    /* ================== */
    /* ================== */

    'dpo_request_url' => 'https://dpoapi.donorperfect.net/prod/xmlrequest.asp',

    'use_stripe_connect' => '1',
    'use_fulfillment' => 'shipping',

    'disable_customer_email_for_products' => '',
    'messenger_donation_welcome_message' => '',
    'messenger_use_minimal_templates' => '0',
    'limit_pledge_campaigns_report_to_created_campaigns' => '0',
    'enable_intercom' => 'all',
    'webhook_account_created_delay' => '300',
    'webhook_account_updated_delay' => '300',
    'webhook_order_completed_delay' => '300',
    'webhook_order_paid_delay' => '300',
    'webhook_contributions_paid_delay' => '300',
    'webhook_contribution_refunded_delay' => '300',
    'passport_personal_access_client_id' => '',
    'passport_personal_access_client_secret' => '',
    'two_factor_authentication' => 'optional',
    'members_exports_chunk_size' => '2500',
];
