<?php

return [
    // --------
    // API
    // --------
    // cart
    'unsupported_type' => 'Unsupported type',
    'cannot_have_more_than_one_donation_item' => "The quantity of a donation item can't be greater than one",
    'code_could_not_be_applied' => 'Code could not be applied.',
    'item_from_another_cart' => 'Item belongs to another cart.',
    'unable_to_upgrade_item' => 'Unable to upgrade item.',
    'email_already_in_use' => 'Email is already in use. Sign-in or use a different email.',
    'email_already_registered' => 'This email address is already registered. Try using a different one.',
    // checkouts
    'cart_missing_payment' => 'Cart requires payment.',
    'invalid_payment_method' => 'Invalid payment method selected.',
    'no_payment_gateway' => 'No payment gateway configured.',
    'no_payment_method' => 'No payment method selected.',
    'no_payment_provider' => 'No payment provider selected.',
    'not_logged_in' => 'You must be logged in to pay with a payment method.',
    'payment_gateway_not_configured' => 'Payment gateway not yet configured',
    'payment_gateway_offline' => 'Payment processing is temporarily offline. Please try again later.',
    // --------
    // ACCOUNTS
    // --------
    'missing_organization_name' => 'Please specify an organization name.',
    'validation' => [
        'account_type_not_found' => 'Specified supporter type does not exist.',
        'enter_valid_email' => 'Please enter a valid email address.',
        'email_already_registered' => ':value has already been registered. Try logging in by resetting your password.',
        'missing_postal_code' => 'Please specify your postal code / zip.',
        'no_account_type_selected' => 'Please specify your supporter type.',
        'nps_value_only_from_1_to_10' => 'Only 1 though 10 are valid NPS values.',
        'password_confirmation_no_match' => 'Your password confirmation does not match.',
        'password_length_8_characters_min' => 'Your password must be at least 8 characters.',
        'password_at_least_1_uppercase_lowercase_and_number' => 'Your password must contain at least one uppercase/lowercase letters and one number.',
        'postal_code_5_characters_min' => 'Your postal code / zip must be at least 5 characters.',
    ],
    // auth
    'account_not_found' => 'Account not found',
    'could_not_verify_postal_code' => 'Could not verify your postal code / zip.',
    'incorrect_login' => 'Incorrect email/password.',
    'device_already_linked' => 'Your device is already linked to an existing account.',
    'donor_id_not_found' => "No match found for id ':donor_id'.",
    'incorrect_login_with_jpanel_link' => 'Incorrect email / password. If you are attempting to log in to the control panel, <a href=":jpanel_url">click here</a>',
    'invalid_signin_attempt' => 'Invalid signup attempt.',
    'emailed_you_a_password_reset_link' => "We've emailed you a password reset link.",
    'emailed_you_a_temporary_password' => "We've emailed you a temporary password.",
    // payment_methods
    'add_another_payment_method_before_removing_current' => 'Please add another payment method before trying to remove your existing payment method.',
    'payment_method_added_to_profile' => 'A payment method (:account_type ending in :account_last_four) was added to your profile.',
    'payment_method_removed_to_profile' => 'A payment method (:account_type ending in :account_last_four) was removed from your profile.',
    // subscriptions
    'amount_greater_than_0_dollars' => 'The amount due must be greater than $0.00.',
    'cannot_cancelled_expired_subscription' => 'Expired subscriptions can not be cancelled.',
    'cannot_cancelled_locked_subscription' => 'Locked subscriptions can not be cancelled.',
    'cannot_update_cancelled_subscription' => 'Cancelled subscriptions can not be updated.',
    'cannot_update_expired_subscription' => 'Expired subscriptions can not be updated.',
    'cannot_update_locked_subscription' => 'Locked subscriptions can not be updated.',
    'error_while_saving_payment_details' => 'An error occurred while saving recurring payment details.',
    'subscription_already_cancelled' => 'Subscription is already cancelled.',
    'subscription_not_found' => 'Subscription not found.',
];
