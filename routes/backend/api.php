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

Route::post('auth/login', 'API\AuthController@login');
Route::post('auth/touch', 'API\AuthController@touch');
// Route::post('auth/validate_token',          'API\AuthController@validateToken');

Route::get('dashboard/checklist', 'API\Dashboard\ChecklistController')->name('dashboard.checklist');
Route::get('dashboard/stats', 'API\Dashboard\StatsController')->name('dashboard.stats');
Route::get('dashboard/charts', 'API\Dashboard\ChartsController')->name('dashboard.charts');
Route::get('dashboard/contributions-by-country', 'API\Dashboard\ContributionsByCountryController');
Route::get('dashboard/{country_code}/contributions-by-region', 'API\Dashboard\ContributionsByRegionController');

Route::get('imports/{import}', 'API\Imports\ImportController@get')->name('api.imports.view');
Route::post('imports/{import}', 'API\Imports\ImportController@store');
Route::post('imports/{import}/file', 'API\Imports\FileController');
Route::post('imports/{import}/field', 'API\Imports\MapFieldController');
Route::post('imports/{import}/validate', 'API\Imports\ValidateFieldMappingController');
Route::post('imports/{import}/analyse', 'API\Imports\AnalysisController@store');
Route::delete('imports/{import}/analyse', 'API\Imports\AnalysisController@destroy');
Route::post('imports/{import}/import', 'API\Imports\ImportController@start');
Route::delete('imports/{import}/import', 'API\Imports\ImportController@destroy');

Route::post('donation/start', 'API\DonationController@startDonation');
Route::post('donation/{order}/payment', 'API\DonationController@startPayment');
Route::get('donation/{order}/nmi_token', 'API\DonationController@showNetworkMerchantsToken');
Route::post('donation/{order}/process', 'API\DonationController@processPayment');
Route::post('donation/{order}/receipt', 'API\DonationController@sendReceipt');

Route::get('order/{order}/nmi_token', 'API\OrderController@showNetworkMerchantsToken');
Route::post('order/{order}/handpoint', 'API\OrderController@handpointTransaction');

Route::post('pos/config.json', 'POSController@getConfig');
Route::any('pos/accounts.json', 'POSController@getAccounts');
Route::any('pos/discounts.json', 'POSController@getDiscounts');
Route::post('pos/products.json', 'POSController@searchProducts');
Route::post('pos/categories.json', 'POSController@listCategories');
Route::post('pos/new', 'POSController@newOrder');
Route::post('pos/{order}/add', 'POSController@addItem');
Route::post('pos/{order}/remove', 'POSController@removeItem');
Route::post('pos/{order}/update', 'POSController@updateOrder');
Route::post('pos/{order}/promos', 'POSController@applyPromos');
Route::post('pos/{order}/payment/initiate', 'API\OrderController@startPayment');
Route::post('pos/{order}/complete', 'POSController@completeOrder');
Route::post('pos/{order}/receipt', 'API\OrderController@getReceipt');

Route::get('kiosks', 'API\KioskController@getKiosks');
Route::post('kiosks', 'API\KioskController@createKiosk');
Route::get('kiosks/{kiosk}', 'API\KioskController@getKiosk');
Route::patch('kiosks/{kiosk}', 'API\KioskController@updateKiosk');
Route::delete('kiosks/{kiosk}', 'API\KioskController@deleteKiosk');
Route::post('kiosks/{kiosk}/session', 'API\KioskSessionController@startKioskSession');

Route::get('donation-forms/global-settings', 'API\DonationFormGlobalSettingsController@show');
Route::post('donation-forms/global-settings', 'API\DonationFormGlobalSettingsController@store');

Route::get('query/payments.json', 'API\QueryController@payments');

Route::get('members/{member}/comments', 'API\MemberCommentsController@index')->name('member.comments.index');
Route::post('members/{member}/comments', 'API\MemberCommentsController@store')->name('member.comments.store');

Route::post('comments/{comment}', 'API\CommentsController@update')->name('comments.update');
Route::delete('comments/{comment}', 'API\CommentsController@destroy')->name('comments.destroy');

Route::get('donation-forms', 'API\DonationFormController@index')->name('api.donation-forms.index');
Route::post('donation-forms', 'API\DonationFormController@store')->name('api.donation-forms.store');
Route::get('donation-forms/{donationForm}', 'API\DonationFormController@show')->name('api.donation-forms.show');
Route::patch('donation-forms/{donationForm}', 'API\DonationFormController@update')->name('api.donation-forms.update');
Route::delete('donation-forms/{donationForm}', 'API\DonationFormController@destroy')->name('api.donation-forms.destroy');
Route::post('donation-forms/{donationForm}/restore', 'API\DonationFormController@restore')->name('api.donation-forms.restore');
Route::post('donation-forms/{donationForm}/replicate', 'API\DonationFormController@replicate')->name('api.donation-forms.replicate');
Route::post('donation-forms/{donationForm}/make-default', 'API\DonationFormController@makeDefault')->name('api.donation-forms.make-default');
Route::patch('donation-forms/{donationForm}/integrations', 'API\DonationFormIntegrationsController@update')->name('api.donation-forms.integrations.update');

Route::get('messenger/phone-numbers/search', 'API\PhoneNumberSearchController')->name('messenger.phone_numbers_search.index');
Route::post('messenger/phone-numbers', 'API\PhoneNumberController@store')->name('messenger.phone_numbers.store');
Route::delete('messenger/phone-numbers/{conversationRecipient}', 'API\PhoneNumberController@destroy')->name('messenger.phone_numbers.destroy');

Route::get('fundraise', 'API\FundraiseEarlyAccessController@show')->name('fundraise.early-access.show');
Route::post('fundraise', 'API\FundraiseEarlyAccessController@store')->name('fundraise.early-access.store');

Route::post('search', 'API\SearchController')->name('search');

Route::post('updates-feed', 'API\UpdatesFeedController')->name('updates-feed');

Route::post('track-page-visit', 'API\TrackPageVisitController')->name('track-page-visit');

// Quickstart
Route::post('quickstart/{task}/skip', '\Ds\Domain\QuickStart\Controllers\SkipTaskController')->name('backend.quickstart.skip');
Route::delete('quickstart/{task}/skip', '\Ds\Domain\QuickStart\Controllers\UnSkipTaskController')->name('backend.quickstart.unskip');

// Settings
Route::get('settings/organization', 'API\Settings\OrganizationController@show')->name('api.settings.organization.show');
Route::patch('settings/organization', 'API\Settings\OrganizationController@store')->name('api.settings.organization.store');
Route::get('settings/branding', 'API\Settings\BrandingController@show')->name('api.settings.branding.show');
Route::patch('settings/branding', 'API\Settings\BrandingController@store')->name('api.settings.branding.store');
Route::get('settings/fundraising', 'API\Settings\FundraisingController@show')->name('api.settings.fundraising.show');
Route::patch('settings/fundraising', 'API\Settings\FundraisingController@store')->name('api.settings.fundraising.store');
Route::get('settings/accept-donations', 'API\Settings\AcceptDonations@show')->name('api.settings.accept-donations.show');
Route::patch('settings/accept-donations', 'API\Settings\AcceptDonations@store')->name('api.settings.accept-donations.store');
Route::patch('settings/accept-donations/disconnect', 'API\Settings\AcceptDonations@disconnect')->name('api.settings.accept-donations.disconnect');
Route::patch('settings/user', 'API\Settings\UserController@store')->name('api.settings.user.store');

Route::post('settings/hotglue/connect', 'API\Settings\HotGlueSettingsController@connect')->name('api.settings.hotglue.connect');
Route::post('settings/hotglue/disconnect', 'API\Settings\HotGlueSettingsController@disconnect')->name('api.settings.hotglue.disconnect');

// Double The Donation
Route::get('double-the-donation/{order}/status', '\Ds\Domain\DoubleTheDonation\Http\Controllers\OrderStatusController');

// Settings
Route::get('settings/organization', 'API\Settings\OrganizationController@show')->name('api.settings.organization.show');
Route::patch('settings/organization', 'API\Settings\OrganizationController@store')->name('api.settings.organization.store');
Route::get('settings/branding', 'API\Settings\BrandingController@show')->name('api.settings.branding.show');
Route::patch('settings/branding', 'API\Settings\BrandingController@store')->name('api.settings.branding.store');
Route::get('settings/fundraising', 'API\Settings\FundraisingController@show')->name('api.settings.fundraising.show');
Route::patch('settings/fundraising', 'API\Settings\FundraisingController@store')->name('api.settings.fundraising.store');
Route::get('settings/accept-donations', 'API\Settings\AcceptDonations@show')->name('api.settings.accept-donations.show');
Route::patch('settings/accept-donations', 'API\Settings\AcceptDonations@store')->name('api.settings.accept-donations.store');
Route::patch('settings/accept-donations/disconnect', 'API\Settings\AcceptDonations@disconnect')->name('api.settings.accept-donations.disconnect');
