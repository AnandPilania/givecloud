<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group([
    'prefix' => 'account',
    'namespace' => 'Account',
], function () {
    Route::get('/', 'AccountController@getAccount');
    Route::patch('/', 'AccountController@updateAccount')->name('api.account.account.update_account');
    Route::post('login', 'AuthController@accountLogin');
    Route::get('logout', 'AuthController@accountLogout');
    Route::post('signup', 'AuthController@signupProcess');
    Route::post('change-password', 'AuthController@changePassword');
    Route::post('reset-password', 'AuthController@resetPasswordProcess');
    Route::post('save-email', 'AuthController@saveEmail');
    Route::post('check-email', 'AuthController@checkEmail');

    Route::get('orders', 'OrdersController@getOrders');

    Route::get('sponsorships', 'SponsorshipsController@getSponorships');
    Route::get('sponsorships/{sponsorship}', 'SponsorshipsController@getSponorship');
    Route::patch('sponsorships/{sponsorship}', 'SponsorshipsController@updateSponsorship');
    Route::delete('sponsorships/{sponsorship}', 'SponsorshipsController@endSponsorship');

    Route::get('payment-methods', 'PaymentMethodsController@getPaymentMethods');
    Route::post('payment-methods', 'PaymentMethodsController@createPaymentMethod');
    Route::get('payment-methods/{payment_method}', 'PaymentMethodsController@getPaymentMethod');
    Route::post('payment-methods/{payment_method}/default', 'PaymentMethodsController@setDefaultPaymentMethod');
    Route::post('payment-methods/{payment_method}/tokenize', 'PaymentMethodsController@tokenizePaymentMethod');
    Route::post('payment-methods/{payment_method}/connect', 'PaymentMethodsController@connectPaymentMethod');
    Route::post('payment-methods/{payment_method}/subscriptions', 'PaymentMethodsController@useForSubscriptions');
    Route::patch('payment-methods/{payment_method}', 'PaymentMethodsController@updatePaymentMethod');
    Route::delete('payment-methods/{payment_method}', 'PaymentMethodsController@deletePaymentMethod');

    Route::post('peer-to-peer-campaigns', 'PeerToPeerCampaignsController@create');
    Route::get('peer-to-peer-campaigns/{hashid}', 'PeerToPeerCampaignsController@get');
    Route::patch('peer-to-peer-campaigns/{hashid}', 'PeerToPeerCampaignsController@update');
    Route::post('peer-to-peer-campaigns/{hashid}/join', 'PeerToPeerCampaignsController@join');

    Route::get('subscriptions', 'SubscriptionsController@getSubscriptions');
    Route::get('subscriptions/{subscription}', 'SubscriptionsController@getSubscription');
    Route::patch('subscriptions/{subscription}', 'SubscriptionsController@updateSubscription');
    Route::delete('subscriptions/{subscription}', 'SubscriptionsController@cancelSubscription');
});

Route::post('carts', 'CartsController@createCart');
Route::get('carts/{cart}', 'CartsController@getCart');
Route::patch('carts/{cart}', 'CartsController@updateCart');
Route::get('carts/{cart}/items', 'CartsController@getItems');
Route::delete('carts/{cart}/items', 'CartsController@emptyCart');
Route::post('carts/{cart}/items', 'CartsController@addItem');
Route::patch('carts/{cart}/items/{item}', 'CartsController@updateItem');
Route::patch('carts/{cart}/items/{item}/upgrade', 'CheckoutsController@upgradeItem');
Route::delete('carts/{cart}/items/{item}', 'CartsController@removeItem');
Route::post('carts/{cart}/register', 'CartsController@createAccount');
Route::post('carts/{cart}/register', 'CartsController@createAccount');
Route::patch('carts/{cart}/checkout', 'CheckoutsController@updateCheckout');
Route::patch('carts/{cart}/dcc', 'CheckoutsController@updateDcc');
Route::patch('carts/{cart}/optin', 'CheckoutsController@updateOptIn');
Route::post('carts/{cart}/capture', 'CheckoutsController@captureToken')->name('api.checkouts.capture_token');
Route::post('carts/{cart}/charge', 'CheckoutsController@chargeToken');
Route::patch('carts/{cart}/complete', 'CheckoutsController@completeCart');
Route::patch('carts/{cart}/match', 'CheckoutsController@updateEmployerMatch');
Route::patch('carts/{cart}/referral', 'CheckoutsController@updateReferralSource');
Route::post('checkouts', 'CheckoutsController@oneClickCheckout');

Route::post('collect', 'AnalyticsController');
Route::get('csrf-token', 'CsrfTokenController');

Route::get('fundraising-pages', 'FundraisingPagesController@getPages');
Route::post('fundraising-pages', 'FundraisingPagesController@createPage');
Route::get('fundraising-pages/{fundraisingPage}', 'FundraisingPagesController@getPage');
Route::patch('fundraising-pages/{fundraisingPage}', 'FundraisingPagesController@updatePage');
Route::delete('fundraising-pages/{fundraisingPage}', 'FundraisingPagesController@deletePage');

Route::get('peer-to-peer-campaigns/{hashid}', 'PeerToPeerCampaignsController@get');
Route::post('peer-to-peer-campaigns/{hashid}/validate-team-join-code', 'PeerToPeerCampaignsController@validateTeamJoinCode');

Route::post('pledge-campaigns/{campaign}/pledge', 'PledgeCampaignsController@createPledge');
Route::get('pledge-campaigns/{campaign}/refresh', 'PledgeCampaignsController@broadcastRefresh');

Route::get('products/{product}', 'ProductsController@getProduct');

Route::group([
    'prefix' => 'services',
    'namespace' => 'Services',
], function () {
    Route::get('locale/countries', 'LocaleController@getCountries');
    Route::get('locale/{country_code}/subdivisions', 'LocaleController@getSubdivisions');
    Route::get('locale/timezones', 'LocaleController@getTimezones');
});
