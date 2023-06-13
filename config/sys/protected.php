<?php

return [
    'Features' => [
        'moduleECommerce' => [
            'label' => 'Ecommerce Enabled',
            'hint' => 'Flag for moduleECommerce',
            'type' => 'select',
            'default' => '0',
            'options' => 'Yes,No',
            'option_values' => '1,0',
        ],
        'feature_ecomm_wallet_pay' => [
            'label' => 'Ecommerce Wallet Pay',
            'hint' => 'Flag for feature_ecomm_wallet_pay.',
            'type' => 'select',
            'default' => '0',
            'options' => 'Yes,No',
            'option_values' => '1,0',
        ],
        'feature_social' => [
            'label' => 'Integrated Social Media',
            'hint' => 'Flag for feature_social',
            'type' => 'select',
            'default' => '',
            'options' => 'Yes,No',
            'option_values' => '1,0',
        ],
        'feature_taxes' => [
            'label' => 'Tax Support',
            'hint' => 'Flag for feature_taxes',
            'type' => 'select',
            'default' => '',
            'options' => 'Yes,No',
            'option_values' => '1,0',
        ],
        'feature_shipping' => [
            'label' => 'Shipping Support',
            'hint' => 'Flag for feature_shipping',
            'type' => 'select',
            'default' => '',
            'options' => 'Yes,No',
            'option_values' => '1,0',
        ],
        'feature_promos' => [
            'label' => 'Promo Code Support',
            'hint' => 'Flag for feature_promos',
            'type' => 'select',
            'default' => '',
            'options' => 'Yes,No',
            'option_values' => '1,0',
        ],
        'feature_stock' => [
            'label' => 'Product Variants',
            'hint' => 'Flag for feature_stock',
            'type' => 'select',
            'default' => '',
            'options' => 'Yes,No',
            'option_values' => '1,0',
        ],
        'feature_customfields' => [
            'label' => 'Custom Fields',
            'hint' => 'Flag for feature_customfields',
            'type' => 'select',
            'default' => '',
            'options' => 'Yes,No',
            'option_values' => '1,0',
        ],
        'feature_trackorder' => [
            'label' => 'Customer Contribution Tracking',
            'hint' => 'Flag for feature_trackorder',
            'type' => 'select',
            'default' => '',
            'options' => 'Yes,No',
            'option_values' => '1,0',
        ],
        'feature_edownloads' => [
            'label' => 'eDownload Support',
            'hint' => 'Flag for feature_edownloads.',
            'type' => 'select',
            'default' => '',
            'options' => 'Yes,No',
            'option_values' => '1,0',
        ],
        'feature_accounts' => [
            'label' => 'Customer Accounts',
            'hint' => 'Flag for feature_accounts.',
            'type' => 'select',
            'default' => '',
            'options' => 'Yes,No',
            'option_values' => '1,0',
        ],
        'feature_buckets' => [
            'label' => 'Bucket Support',
            'hint' => 'Flag for feature_buckets.',
            'type' => 'select',
            'default' => '',
            'options' => 'Yes,No',
            'option_values' => '1,0',
        ],
        'feature_membership' => [
            'label' => 'Memberships',
            'hint' => 'Flag for feature_membership.',
            'type' => 'select',
            'default' => '',
            'options' => 'Yes,No',
            'option_values' => '1,0',
        ],
        'feature_sponsorship' => [
            'label' => 'Child Sponsorship Support',
            'hint' => 'Flag for feature_sponsorship.',
            'type' => 'select',
            'default' => '',
            'options' => 'Yes,No',
            'option_values' => '1,0',
        ],
        'feature_check_ins' => [
            'label' => 'Event Management (Check-ins)',
            'hint' => 'Flag for feature_check_ins.',
            'type' => 'select',
            'default' => '0',
            'options' => 'Yes,No',
            'option_values' => '1,0',
        ],
        'feature_onepage' => [
            'label' => 'One-Page Checkout',
            'hint' => 'Flag for feature_onepage.',
            'type' => 'select',
            'default' => '0',
            'options' => 'Yes,No',
            'option_values' => '1,0',
        ],
        'feature_tax_receipt' => [
            'label' => 'Tax Receipts',
            'hint' => 'Flag for feature_tax_receipt.',
            'type' => 'select',
            'default' => '0',
            'options' => 'Yes,No',
            'option_values' => '1,0',
        ],
        'feature_sites' => [
            'label' => 'Sites',
            'hint' => 'Flag for feature_sites.',
            'type' => 'select',
            'default' => '0',
            'options' => 'Yes,No',
            'option_values' => '1,0',
        ],
        'feature_edit_order_items' => [
            'label' => 'Edit Contribution Items',
            'hint' => 'Flag for feature_edit_order_items.',
            'type' => 'select',
            'default' => '0',
            'options' => 'Yes,No',
            'option_values' => '1,0',
        ],
        'feature_allow_previewing_new_ui' => [
            'label' => 'Allow Previewing New UI',
            'hint' => 'Flag for feature_allow_previewing_new_ui.',
            'type' => 'select',
            'default' => '0',
            'options' => 'Yes,No',
            'option_values' => '1,0',
        ],
        'feature_linked_products' => [
            'label' => 'Linked Products',
            'hint' => 'Flag for feature_linked_products.',
            'type' => 'select',
            'default' => '0',
            'options' => 'Yes,No',
            'option_values' => '1,0',
        ],
        'feature_add_from_vault' => [
            'label' => 'Add Payment Methods By Vault ID',
            'hint' => 'Flag for feature_add_from_vault.',
            'type' => 'select',
            'default' => '0',
            'options' => 'Yes,No',
            'option_values' => '1,0',
        ],
        'feature_double_the_donation' => [
            'label' => 'Double The Donation',
            'hint' => 'Flag for feature_double_the_donation.',
            'type' => 'select',
            'default' => '0',
            'options' => 'Yes,No',
            'option_values' => '1,0',
        ],
        'feature_fundraising_pages' => [
            'label' => 'Peer-to-Peer',
            'hint' => 'Flag for feature_fundraising_pages.',
            'type' => 'select',
            'default' => '0',
            'options' => 'Yes,No',
            'option_values' => '1,0',
        ],
        'feature_kiosks' => [
            'label' => 'Kiosks',
            'hint' => 'Flag for feature_kiosks.',
            'type' => 'select',
            'default' => '0',
            'options' => 'Yes,No',
            'option_values' => '1,0',
        ],
        'feature_metadata' => [
            'label' => 'Metadata',
            'hint' => 'Flag for feature_metadata.',
            'type' => 'select',
            'default' => '0',
            'options' => 'Yes,No',
            'option_values' => '1,0',
        ],
        'feature_messenger' => [
            'label' => 'Messenger',
            'hint' => 'Flag for feature_messenger.',
            'type' => 'select',
            'default' => '0',
            'options' => 'Yes,No',
            'option_values' => '1,0',
        ],
        'feature_pledges' => [
            'label' => 'Pledges',
            'hint' => 'Flag for feature_pledges.',
            'type' => 'select',
            'default' => '0',
            'options' => 'Yes,No',
            'option_values' => '1,0',
        ],
        'feature_virtual_events' => [
            'label' => 'Virtual Events',
            'hint' => 'Flag for feature_virtual_events.',
            'type' => 'select',
            'default' => '1',
            'options' => 'Yes,No',
            'option_values' => '1,0',
        ],
        'feature_virtual_events_mux_streaming' => [
            'label' => 'Virtual Events Mux Streaming',
            'hint' => 'Flag for feature_virtual_events_mux_streaming.',
            'type' => 'select',
            'default' => '1',
            'options' => 'Yes,No',
            'option_values' => '1,0',
        ],
        'zapier_enabled' => [
            'label' => 'Zapier',
            'hint' => '',
            'type' => 'select',
            'default' => '0',
            'options' => 'Disabled,Enabled',
            'option_values' => '0,1',
        ],
        'feature_embedded_donation_forms' => [
            'label' => 'Embeddable Donation Forms',
            'hint' => 'Flag for feature_embedded_donation_forms.',
            'type' => 'select',
            'default' => '1',
            'options' => 'Yes,No',
            'option_values' => '1,0',
        ],
        'feature_account_notes' => [
            'label' => 'Account notes',
            'hint' => 'Enables account notes',
            'type' => 'select',
            'default' => '0',
            'options' => 'Yes,No',
            'option_values' => '1,0',
        ],
        'feature_salesforce' => [
            'label' => 'Salesforce Integration (Legacy)',
            'hint' => 'Enables Salesforce Integration',
            'type' => 'select',
            'default' => '0',
            'options' => 'Disabled,Enabled',
            'option_values' => '0,1',
        ],
        'feature_hotglue_salesforce' => [
            'label' => 'Salesforce Integration (Through HotGlue)',
            'hint' => 'Enables Salesforce Integration',
            'type' => 'select',
            'default' => '0',
            'options' => 'Disabled,Enabled',
            'option_values' => '0,1',
        ],
        'feature_hotglue_mailchimp' => [
            'label' => 'Mailchimp Integration (Through HotGlue)',
            'hint' => 'Enables Mailchimp Integration',
            'type' => 'select',
            'default' => '0',
            'options' => 'Disabled,Enabled',
            'option_values' => '0,1',
        ],
        'feature_hotglue_hubspot' => [
            'label' => 'Hubspot Integration (Through HotGlue)',
            'hint' => 'Enables Hubspot Integration',
            'type' => 'select',
            'default' => '0',
            'options' => 'Disabled,Enabled',
            'option_values' => '0,1',
        ],
        'feature_imports' => [
            'label' => 'Imports',
            'hint' => 'Enables Imports',
            'type' => 'select',
            'default' => '0',
            'options' => 'Disabled,Enabled',
            'option_values' => '0,1',
        ],
        'feature_supporter_search' => [
            'label' => 'Supporter Search',
            'hint' => 'Enables Supporter Search',
            'type' => 'select',
            'default' => '0',
            'options' => 'Disabled,Enabled',
            'option_values' => '0,1',
        ],
        'feature_fundraising_forms' => [
            'label' => 'Fundraising Forms',
            'hint' => 'Enables the new Fundraise/Donation Forms',
            'type' => 'select',
            'default' => '0',
            'options' => 'Disabled,Enabled',
            'option_values' => '0,1',
        ],
        'feature_dcc_ai_plus' => [
            'label' => 'DCC AI +',
            'hint' => 'Enables the DCC AI+',
            'type' => 'select',
            'default' => '1',
            'options' => 'Disabled,Enabled',
            'option_values' => '0,1',
        ],
        'feature_flatfile_supporter_imports' => [
            'label' => 'Supporter Imports',
            'hint' => 'Enables Supporter Imports through our Flatfile partner',
            'type' => 'select',
            'default' => '0',
            'options' => 'Disabled,Enabled',
            'option_values' => '0,1',
        ],
        'feature_flatfile_sponsorships_imports' => [
            'label' => 'Sponsorships Imports',
            'hint' => 'Enables Sponsorships Imports through our Flatfile partner',
            'type' => 'select',
            'default' => '0',
            'options' => 'Disabled,Enabled',
            'option_values' => '0,1',
        ],
        'feature_flatfile_contributions_imports' => [
            'label' => 'Contributions Imports',
            'hint' => 'Enables Contributions Imports through our Flatfile partner',
            'type' => 'select',
            'default' => '0',
            'options' => 'Disabled,Enabled',
            'option_values' => '0,1',
        ],
        'feature_unified_contributions_listing' => [
            'label' => 'Unified Contributions Listing',
            'hint' => 'Enables unified contribution listing',
            'type' => 'select',
            'default' => '0',
            'options' => 'Disabled,Enabled',
            'option_values' => '0,1',
        ],
    ],

    'Fundraising Experiences' => [
        'feature_fundraising_forms_peer_to_peer' => [
            'label' => 'Fundraising Forms P2P',
            'hint' => 'Enables the P2P using Fundraising Forms',
            'type' => 'select',
            'default' => '0',
            'options' => 'Disabled,Enabled',
            'option_values' => '0,1',
        ],
        'feature_fundraising_forms_standard_layout' => [
            'label' => 'Fundraising Forms Standard Layout',
            'hint' => 'Enables the Fundraising Form Standard Layout',
            'type' => 'select',
            'default' => '1',
            'options' => 'Disabled,Enabled',
            'option_values' => '0,1',
        ],
    ],

    'Sponsorship' => [
        'sponsorship_database_name' => [
            'label' => 'Master Sponsorship Database',
            'hint' => 'The database/account_name of the master sponsorship site.',
            'type' => 'text',
            'default' => '',
        ],
    ],

    'Shipping: Integration' => [
        'shipping_handler' => [
            'label' => 'Shipping Handler',
            'hint' => 'Will your site use tiered shipping pricing or courier rated pricing?',
            'type' => 'select',
            'default' => 'tiered',
            'options' => 'Tiered Shipping, Courier Rated',
            'option_values' => 'tiered,courier',
        ],
        'shipping_taxes_apply' => [
            'label' => 'Shipping Taxes',
            'hint' => 'This must exist in DPO before setting the value here.  Values are case sEnSiTiVe.',
            'type' => 'select',
            'default' => '0',
            'options' => "Apply taxes to shipping charges,DON'T APPLY taxes to shipping charges",
            'option_values' => '1,0',
        ],
        'shipping_linked_items' => [
            'label' => 'Shipping Bundles/Linked Items',
            'hint' => "How shippable and free shipping for a bundle's Contribution Item and the Contribution Items for the bundle's linked items should be calculated.",
            'type' => 'select',
            'default' => 'both',
            'options' => 'Both,Bundle',
            'option_values' => 'both,bundle',
        ],
        'shipping_from_state' => [
            'label' => 'Ship From State/Prov.',
            'hint' => '',
            'type' => 'text',
            'default' => '',
        ],
        'shipping_from_zip' => [
            'label' => 'Ship From Zip/Postal',
            'hint' => '',
            'type' => 'text',
            'default' => '',
        ],
        'shipping_from_country' => [
            'label' => 'Ship From Country',
            'hint' => '',
            'type' => 'text',
            'default' => '',
        ],
    ],

    'Shipping: Canada Post' => [
        'shipping_canadapost_enabled' => [
            'label' => 'Canada Post',
            'hint' => '',
            'type' => 'select',
            'default' => '0',
            'options' => 'Disabled,Enabled',
            'option_values' => '0,1',
        ],
        'shipping_canadapost_customer_number' => [
            'label' => 'Customer Number',
            'hint' => '',
            'type' => 'text',
            'default' => '',
        ],
        'shipping_canadapost_user' => [
            'label' => 'Username',
            'hint' => '',
            'type' => 'text',
            'default' => '',
        ],
        'shipping_canadapost_pass' => [
            'label' => 'Password',
            'hint' => '',
            'type' => 'password',
            'default' => '',
        ],
    ],

    'Shipping: UPS' => [
        'shipping_ups_enabled' => [
            'label' => 'UPS',
            'hint' => '',
            'type' => 'select',
            'default' => '0',
            'options' => 'Disabled,Enabled',
            'option_values' => '0,1',
        ],
        'shipping_ups_access_key' => [
            'label' => 'Access Key',
            'hint' => '',
            'type' => 'text',
            'default' => '',
        ],
        'shipping_ups_user' => [
            'label' => 'Username',
            'hint' => '',
            'type' => 'text',
            'default' => '',
        ],
        'shipping_ups_pass' => [
            'label' => 'Password',
            'hint' => '',
            'type' => 'password',
            'default' => '',
        ],
        'shipping_ups_servicecodes' => [
            'label' => 'Service Codes',
            'hint' => '',
            'type' => 'multi_select',
            'default' => '',
            'options' => implode(',', array_values(\Ds\Domain\Commerce\Shipping\Carriers\UPS::getServices())),
            'option_values' => implode(',', array_keys(\Ds\Domain\Commerce\Shipping\Carriers\UPS::getServices())),
        ],
    ],

    'Shipping: Fedex' => [
        'shipping_fedex_enabled' => [
            'label' => 'Fedex',
            'hint' => '',
            'type' => 'select',
            'default' => '0',
            'options' => 'Disabled,Enabled',
            'option_values' => '0,1',
        ],
        'shipping_fedex_key' => [
            'label' => 'Api Key',
            'hint' => '',
            'type' => 'text',
            'default' => '',
        ],
        'shipping_fedex_account' => [
            'label' => 'Account',
            'hint' => '',
            'type' => 'text',
            'default' => '',
        ],
        'shipping_fedex_meter' => [
            'label' => 'Meter',
            'hint' => '',
            'type' => 'text',
            'default' => '',
        ],
        'shipping_fedex_pass' => [
            'label' => 'Password',
            'hint' => '',
            'type' => 'password',
            'default' => '',
        ],
        'shipping_fedex_net_discount' => [
            'label' => 'Rate Type',
            'hint' => '',
            'type' => 'select',
            'default' => '0',
            'options' => 'Account,List',
            'option_values' => '0,1',
        ],
        'shipping_fedex_servicecodes' => [
            'label' => 'Services',
            'hint' => '',
            'type' => 'multi_select',
            'default' => '',
            'options' => implode(',', array_values(\Ds\Domain\Commerce\Shipping\Carriers\FedEx::getServices())),
            'option_values' => implode(',', array_keys(\Ds\Domain\Commerce\Shipping\Carriers\FedEx::getServices())),
        ],
    ],

    'Shipping: USPS' => [
        'shipping_usps_enabled' => [
            'label' => 'USPS',
            'hint' => '',
            'type' => 'select',
            'default' => '0',
            'options' => 'Disabled,Enabled',
            'option_values' => '0,1',
        ],
        'shipping_usps_user' => [
            'label' => 'Username',
            'hint' => '',
            'type' => 'text',
            'default' => '',
        ],
        'shipping_usps_pass' => [
            'label' => 'Password',
            'hint' => '',
            'type' => 'password',
            'default' => '',
        ],
        'shipping_usps_classids' => [
            'label' => 'CLASSIDS',
            'hint' => '',
            'type' => 'multi_select',
            'default' => '3,1,4,6,7',
            'options' => implode(',', array_values(\Ds\Domain\Commerce\Shipping\Carriers\USPS::getClassIds())),
            'option_values' => implode(',', array_keys(\Ds\Domain\Commerce\Shipping\Carriers\USPS::getClassIds())),
        ],
        'shipping_usps_interids' => [
            'label' => 'International CLASSIDS',
            'hint' => '',
            'type' => 'multi_select',
            'default' => '12,1,26,2,11,9,16,24,25,15',
            'options' => implode(',', array_values(\Ds\Domain\Commerce\Shipping\Carriers\USPS::getInternationalClassIds())),
            'option_values' => implode(',', array_keys(\Ds\Domain\Commerce\Shipping\Carriers\USPS::getInternationalClassIds())),
        ],
    ],

    'Advanced Settings' => [
        'is_suspended' => [
            'label' => 'Account Status',
            'hint' => 'Is the account active or suspended?',
            'type' => 'select',
            'default' => '0',
            'options' => 'Active,Suspended',
            'option_values' => '0,1',
        ],
        'enable_admin_logrocket' => [
            'label' => 'JPANEL LogRocket',
            'hint' => 'Enables the JPANEL LogRocket integration',
            'type' => 'select',
            'default' => '1',
            'options' => 'Disabled,Enabled',
            'option_values' => '0,1',
        ],
        'email_sender_required' => [
            'label' => 'Require Sender Header',
            'hint' => 'Whether or not to include a Sender header when sending email. This should only be turned off if we have configured DKIM for the subscriber on our SendGrid.',
            'type' => 'select',
            'default' => '1',
            'options' => 'Yes,No',
            'option_values' => '1,0',
        ],
        'local_currencies' => [
            'label' => 'Local Currencies',
            'hint' => '',
            'type' => 'selectize',
            'default' => '',
            'options' => collect(\Ds\Domain\Commerce\Currency::getCurrencies())->pluck('name')->join(','),
            'option_values' => collect(\Ds\Domain\Commerce\Currency::getCurrencies())->pluck('code')->join(','),
        ],
        'money_with_currency_preference' => [
            'label' => 'Money With Currecy Preference',
            'hint' => 'When when formatting money and a currency code can be included, should it?',
            'type' => 'select',
            'default' => '0',
            'options' => 'Always,No preference',
            'option_values' => '1,0',
        ],
        'use_stripe_connect' => [
            'label' => 'Use Stripe Connect',
            'hint' => 'Whether or not to use Stripe Connect when connecting a Stripe account.',
            'type' => 'select',
            'default' => '1',
            'options' => 'Yes,No',
            'option_values' => '1,0',
        ],
        'use_fulfillment' => [
            'label' => 'Fulfillment Behaviour',
            'hint' => 'Fulfillment setting',
            'type' => 'select',
            'default' => 'shipping',
            'options' => 'Shipping,Always,Never',
            'option_values' => 'shipping,always,never',
        ],
        'dcc_stripe_application_fee_billing' => [
            'label' => 'Enable Stripe "Application Fee" Billing',
            'hint' => 'Collect DCC as an Application Fee on eligible Stripe payments in lieu of Platform Fees.',
            'type' => 'select',
            'default' => '0',
            'options' => 'Yes,No',
            'option_values' => '1,0',
        ],
        'public_payments_disabled_until' => [
            'label' => 'Stop Payments Until',
            'type' => 'text',
            'default' => '',
        ],
        'disable_customer_email_for_products' => [
            'label' => 'Disable Customer Email for Products',
            'hint' => 'Prevents the customer email from being sent for contributions containing these products.',
            'type' => 'text',
            'default' => '',
            'options' => '',
            'option_values' => '',
        ],
        'messenger_donation_welcome_message' => [
            'label' => 'Messager Custom Welcome Meesage',
            'hint' => 'Sent to unrecognized numbers with a link to setup an account.',
            'type' => 'text',
            'default' => '',
            'options' => '',
            'option_values' => '',
        ],
        'messenger_use_minimal_templates' => [
            'label' => 'Use Minimal Messenger Templates',
            'type' => 'select',
            'default' => '0',
            'options' => 'Yes,No',
            'option_values' => '1,0',
        ],
        'rpp_donorperfect' => [
            'label' => "Allow DonorPerfect RPP's",
            'hint' => '',
            'type' => 'select',
            'default' => '0',
            'options' => 'Yes,No',
            'option_values' => '1,0',
        ],
        'webhook_account_created_delay' => [
            'label' => 'Delay firing "account_created" webhook',
            'hint' => '',
            'type' => 'select',
            'default' => '300',
            'options' => 'Instant,5 mins,20 mins',
            'option_values' => '0,300,1200',
        ],
        'webhook_account_updated_delay' => [
            'label' => 'Delay firing "account_updated" webhook',
            'hint' => '',
            'type' => 'select',
            'default' => '300',
            'options' => 'Instant,5 mins,20 mins',
            'option_values' => '0,300,1200',
        ],
        'webhook_order_completed_delay' => [
            'label' => 'Delay firing "order_completed" webhook',
            'hint' => '',
            'type' => 'select',
            'default' => '300',
            'options' => 'Instant,5 mins,20 mins',
            'option_values' => '0,300,1200',
        ],
        'webhook_order_paid_delay' => [
            'label' => 'Delay firing "contribution_paid" webhook',
            'hint' => '',
            'type' => 'select',
            'default' => '300',
            'options' => 'Instant,5 mins,20 mins',
            'option_values' => '0,300,1200',
        ],
        'preserve_amount_on_variant_change' => [
            'label' => 'Preserve amount when switching variants',
            'hint' => '',
            'type' => 'select',
            'default' => '0',
            'options' => 'Yes,No',
            'option_values' => '1,0',
        ],
        'limit_pledge_campaigns_report_to_created_campaigns' => [
            'label' => 'Enable limited Pledge Campaign reporting',
            'hint' => 'Limits report to Pledge Campaigns created by the authenticated user',
            'type' => 'select',
            'default' => '0',
            'options' => 'Yes,No',
            'option_values' => '1,0',
        ],
        'enable_intercom' => [
            'label' => 'Enable Intercom for',
            'hint' => 'Who should have access to real-time chat with our support team',
            'type' => 'select',
            'default' => 'all',
            'options' => 'All Admin Panel Users,Only Account Owners,No One',
            'option_values' => 'all,owners,none',
        ],
        'platform_fee_types' => [
            'label' => 'Platform Fee Types',
            'hint' => 'A JSON-encoded string containing the platform fee types.',
            'type' => 'long_text',
            'default' => '',
        ],
    ],

    'External Donations Display' => [
        'external_donations_start_date' => [
            'label' => 'Beginning Date',
            'hint' => 'By selecting a date, you are filtering the gift list to only show gifts made after this date.',
            'type' => 'date',
            'default' => '',
        ],
        'external_donations_end_date' => [
            'label' => 'End Date',
            'hint' => 'By selecting a date, you are filtering the gift list to only show gifts made before this date.',
            'type' => 'date',
            'default' => '',
        ],
        'external_donations_gl_codes' => [
            'label' => 'GL Codes',
            'hint' => 'Which GL Codes to filter the external donations on. If left blank, will show gifts for all GL Codes. To add multiple, separate with commas. (Eg, "GENERAL, BUILDING")',
            'type' => 'text',
            'default' => '',
            'options' => '',
            'option_values' => '',
        ],
        'external_donations_gift_types' => [
            'label' => 'Gift Types',
            'hint' => 'Which Gift Types to filter the external donations on. If left blank, will show gifts for all Gift Types.  To add multiple, separate with commas. (Eg, VISA, INTERAC)',
            'type' => 'text',
            'default' => '',
            'options' => '',
            'option_values' => '',
        ],
        'force_recurring_payments_to_extend_memberships' => [
            'label' => 'HACK: Force Recurring Payments to Extend Memberships',
            'hint' => 'WARNING: This setting is a nuclear bomb. It was created for california water fowl. It should not be used for anyone else without express permission from Eng. You have been warned.',
            'type' => 'select',
            'default' => '0',
            'options' => 'Yes,No',
            'option_values' => '1,0',
        ],
    ],
];