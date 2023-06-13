<?php

return [
    'General' => [
        'thumbnail_size' => [
            'label' => 'Image Thumbnail Size',
            'hint' => "WxH. NO SPACES. '300x?' is the recommended setting (fixed 300 width and variable height).",
            'type' => 'text',
            'default' => '300x?',
        ],
        'thumbnail_crop' => [
            'label' => 'Image Thumbnail Crop',
            'type' => 'select',
            'default' => 'entropy',
            'options' => 'Top,Center,Bottom,Left,Right,Attention,Entropy,Face',
            'option_values' => 'top,center,bottom,left,right,attention,entropy,face',
        ],
    ],

    'Social Media' => [
        'fbAdminID' => [
            'label' => 'Facebook Admin Account ID',
            'hint' => 'See the support site for information on how to determine your Facebook Admin Account ID number.',
            'type' => 'text',
            'default' => '',
        ],
        'fbAppId' => [
            'label' => 'Facebook App ID',
            'hint' => 'See the support site for information on how to create a Facebook App ID. This is required if you want to use FB social plugins.',
            'type' => 'text',
            'default' => '',
        ],
    ],

    'Synonyms' => [
        'ecomm_syn_author' => [
            'label' => 'Product Filter Label',
            'hint' => '',
            'type' => 'text',
            'default' => '',
        ],
        'synonym_province' => [
            'label' => 'Province/State',
            'hint' => '',
            'type' => 'text',
            'default' => '',
        ],
        'cart_synonym' => [
            'label' => 'Cart',
            'hint' => "Default cart label.  You can also override 'Add to Cart' buttons on a per-product basis.",
            'type' => 'text',
            'default' => '',
        ],
        'syn_sponsorship_child' => [
            'label' => 'Sponsorship: Child',
            'hint' => 'Ex: Child',
            'type' => 'text',
            'default' => 'Child',
        ],
        'syn_sponsorship_children' => [
            'label' => 'Sponsorship: Children (Plural)',
            'hint' => 'Ex: Children',
            'type' => 'text',
            'default' => 'Children',
        ],
        'syn_group' => [
            'label' => 'Group',
            'hint' => 'Ex: Membership',
            'type' => 'text',
            'default' => 'Membership',
        ],
        'syn_groups' => [
            'label' => 'Groups (Plural)',
            'hint' => 'Ex: Memberships',
            'type' => 'text',
            'default' => 'Memberships',
        ],
        'syn_group_member' => [
            'label' => 'Group Member',
            'hint' => 'Ex: Member',
            'type' => 'text',
            'default' => 'Member',
        ],
        'syn_group_members' => [
            'label' => 'Group Members (Plural)',
            'hint' => 'Ex: Members',
            'type' => 'text',
            'default' => 'Members',
        ],
    ],

    'eComm: General' => [
        'use_category_images' => [
            'label' => 'Category Images',
            'hint' => 'If enabled, a category header image will be displayed for each category.  You will need to upload appropriate category header images for each category.',
            'type' => 'select',
            'default' => '',
            'options' => 'Enabled,Disabled',
            'option_values' => '1,0',
        ],
        'category_default_order_by' => [
            'label' => 'Default Category Order-By',
            'hint' => 'The default sorting of products in each category.',
            'type' => 'select',
            'default' => 'publish_start_date DESC',
            'options' => 'Newest to Oldest,Oldest to Newest,Price Lowest to Highest, Price: Highest to Lowest,Name,Filter,Category',
            'option_values' => 'publish_start_date DESC,publish_start_date,actualprice,actualprice DESC,name,author,category_name',
        ],
    ],

    'eComm: Checkout Screen' => [
        'payment_day_options' => [
            'label' => 'Recurring Payment Day of Month Options',
            'hint' => '',
            'type' => 'multi_select',
            'default' => '',
            'options' => '1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28',
            'option_values' => '1,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25,26,27,28',
        ],
        'payment_day_of_week_options' => [
            'label' => 'Recurring Payment Day of Week Options',
            'hint' => '',
            'type' => 'multi_select',
            'default' => '',
            'options' => 'Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday',
            'option_values' => '1,2,3,4,5,6,7',
        ],
        'min_days_before_recurring_start_date' => [
            'label' => 'Min Days to First Recurring Payment',
            'hint' => 'The minimum number of days after the first transation required before the first recurring payment is scheduled.',
            'type' => 'text',
            'default' => '',
        ],
        'allow_signup_in_checkout' => [
            'label' => "Show 'Create an Account'",
            'hint' => '',
            'type' => 'select',
            'default' => '',
            'options' => 'Yes,No',
            'option_values' => '1,0',
        ],
        'checkout_min_value' => [
            'label' => 'Minimum checkout value',
            'hint' => 'Purchases below this threshold will not be processed. This does not affect $0 contributions or registrations.',
            'type' => 'text',
            'default' => '',
            'options' => '',
            'option_values' => '',
        ],
    ],

    'eComm: Order Fullfillment' => [
        'packing_slip_contribution_syn' => [
            'label' => 'Packing Slip Contribution Synonym',
            'hint' => 'This shows at the top of a packing slip. If the value is "Contribution", the header of the packing slip will say "Contribution #12345678".',
            'type' => 'text',
            'default' => '',
        ],
        'packing_slip_corporate_header' => [
            'label' => 'Packing Slip Header',
            'hint' => '',
            'type' => 'html',
            'default' => '',
        ],
    ],

    'eComm: Account Updater' => [
        'account_updater_reactivate_suspended' => [
            'label' => "Reactivate 'Suspended' Profiles",
            'hint' => "Will reactivate any 'Suspended' Recurring Payment Profiles linked to a given Payment Method updated by Account Updater.",
            'type' => 'select',
            'default' => '0',
            'options' => 'Yes,No',
            'option_values' => '1,0',
        ],
        'account_updater_max_last_payment_days_ago' => [
            'label' => 'With a Payment Less Than X Days Ago',
            'hint' => "Only reactivate a 'Suspended' Recurring Payment Profile if there's been a successful payment in the last X days.",
            'type' => 'select',
            'default' => '30',
            'options' => '0,7,14,30,45,60,75,90,180,365',
            'option_values' => '0,7,14,30,45,60,75,90,180,365',
        ],
    ],

    'eComm: Recurring Payment Profiles' => [
        /*
        'rpp_process_at' => array(
            'label' => "Process payments daily at",
            'hint' => "The processing of payments will be delayed until the next available time if your selected processing time fails within a scheduled maintenance window.",
            'type' => "select",
            'default' => "0500",
            'options' => "12am,1am,2am,3am,4am,5am,6am,7am,8am,9am,10am,11am,12pm,1pm,2pm,3pm,4pm,5pm,6pm,7pm,8pm,9pm,10pm,11pm",
            'option_values' => "0000,0100,0200,0300,0400,0500,0600,0700,0800,0900,1000,1100,1200,1300,1400,1500,1600,1700,1800,1900,2000,2100,2200,2300"
        ),
        */
        'rpp_retry_attempts' => [
            'label' => 'Payment attempts',
            'hint' => 'Number of payments attempts that can fail before a profile is automatically suspended.',
            'type' => 'select',
            'default' => '1',
            'options' => '0,1,2,3,4,5,6,7,8',
            'option_values' => '0,1,2,3,4,5,6,7,8',
        ],
        'rpp_retry_interval' => [
            'label' => 'Retry interval (in days)',
            'hint' => 'Number of days to wait before attempting to reprocess a failed scheduled payment.',
            'type' => 'select',
            'default' => '7',
            'options' => '0,1,2,3,4,5,6,7,8',
            'option_values' => '0,1,2,3,4,5,6,7,8',
        ],
        /*
        'rpp_auto_bill_out_amt' => array(
            'label' => "Automatically bill outstanding balance",
            'hint' => "Indicates whether you would like to automatically bill the outstanding balance amount in the next billing cycle. The outstanding balance is the total amount of any previously failed scheduled payments that have yet to be successfully paid.",
            'type' => "select",
            'default' => "0",
            'options' => "No Auto Bill,Add To Next Billing",
            'option_values' => "0,1"
        ),
        'rpp_nsf_fee' => array(
            'label' => "NSF Fee",
            'hint' => "",
            'type' => "text",
            'default' => ""
        )
        */
    ],

    'Mailing Labels' => [
        'ml_enabled' => [
            'label' => 'Mailing Labels',
            'hint' => 'Enable mailing labels.',
            'type' => 'select',
            'default' => '0',
            'options' => 'Enabled,Disabled',
            'option_values' => '1,0',
        ],
        'ml_label_template' => [
            'label' => 'Label Template',
            'hint' => 'Merge Codes:[[name_formatted]], [[address_formatted]], [[address]], [[city]], [[state]], [[zip]], [[country]]',
            'type' => 'html',
            'default' => '<p>TOS: [[name_formatted]]<br />[[address_formatted]]</p>',
        ],
        'ml_page_label_count' => [
            'label' => 'Number Of Labels Per Page',
            'hint' => 'Number of labels to display on a page.',
            'type' => 'text',
            'default' => '30',
        ],
        'ml_page_top_margin' => [
            'label' => 'Page Top Margin',
            'hint' => 'The top margin of the page (Measurement in inches).',
            'type' => 'text',
            'default' => '0.5',
        ],
        'ml_page_left_margin' => [
            'label' => 'Page Left Margin',
            'hint' => 'The left margin of the page (Measurement in inches).',
            'type' => 'text',
            'default' => '0.5',
        ],
        'ml_label_width' => [
            'label' => 'Label Width',
            'hint' => 'Width of an individual label (Measurement in inches).',
            'type' => 'text',
            'default' => '2',
        ],
        'ml_label_height' => [
            'label' => 'Label Height',
            'hint' => 'Height of an individual label (Measurement in inches).',
            'type' => 'text',
            'default' => '0.5',
        ],
        'ml_page_column_spacing' => [
            'label' => 'Margin Between Label Columns',
            'hint' => 'The margin between the label columns (Measurement in inches).',
            'type' => 'text',
            'default' => '0.05',
        ],
        'ml_page_row_spacing' => [
            'label' => 'Margin Between Label Rows',
            'hint' => 'The margin between the label rows (Measurement in inches).',
            'type' => 'text',
            'default' => '0',
        ],
        'ml_label_inner_margin' => [
            'label' => 'Inner Label Margin',
            'hint' => 'The margin around the text inside of an individual label (Measurement in inches).',
            'type' => 'text',
            'default' => '0.09',
        ],
        'ml_label_font_size' => [
            'label' => 'Label font size',
            'hint' => 'The font size of the label text (Font size in px).',
            'type' => 'text',
            'default' => '9',
        ],
    ],

    'Other' => [
        'login_success_contingency_url' => [
            'label' => 'Succesful Login Contingency Url',
            'hint' => "When a user logs in, they are taken to the page they were on before they tried logging in. If the previous page can't be determined, THIS URL WILL BE USED.",
            'type' => 'text',
            'default' => '/',
        ],
    ],
];
