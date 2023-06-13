<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */

Route::any('/', 'DefaultController@handleHomePage')->name('frontend.home');

Route::get('a/{token}', 'AccountsController@autologin')->name('autologin');
Route::get('account/home', 'AccountsController@home')->name('frontend.accounts.home');
Route::get('account/login', 'AccountsController@login')->name('frontend.accounts.login');
Route::get('account/logout', 'AccountsController@logout')->name('frontend.accounts.logout');
Route::get('account/register', 'AccountsController@register')->name('frontend.accounts.register');
Route::get('account/reset-password', 'AccountsController@reset_password');
Route::get('account/change-password', 'AccountsController@change_password');

// Social Login
Route::get('account/social/redirect/{provider}', 'SocialLoginController@redirect')->name('frontend.account.social.redirect');
Route::get('account/social/transparent/{provider}', 'SocialLoginController@transparent')->name('frontend.account.social.transparent');
Route::get('account/social/callback', 'SocialLoginController@callback')->name('frontend.account.social.callback');
Route::get('account/social/revoke/{provider}', 'SocialLoginController@revoke')->name('frontend.account.social.revoke');

Route::get('account/profile', 'AccountsController@profile')->name('accounts.profile');
Route::get('account/history', 'OrdersController@index');
Route::get('account/payment-methods', 'PaymentMethodsController@index');
Route::get('account/payment-methods/add', 'PaymentMethodsController@show');
Route::get('account/payment-methods/{id}', 'PaymentMethodsController@show');
Route::any('account/payment-methods/{id}/connect', 'PaymentMethodsController@tokenizeReturn');
Route::get('account/payment-methods/{id}/tokenize/cancel', 'PaymentMethodsController@tokenizeCancel');
Route::get('account/sponsorships', 'SponsorsController@index');
Route::get('account/sponsorships/{id}', 'SponsorsController@sponsorship');
Route::post('account/sponsorships/{id}', 'SponsorsController@end');
Route::get('account/subscriptions', 'RecurringPaymentsController@index');
Route::get('account/subscriptions/{profile_id}', 'RecurringPaymentsController@view');
Route::get('account/subscriptions/{profile_id}/edit', 'RecurringPaymentsController@edit');
Route::get('account/subscriptions/{profile_id}/cancel', 'RecurringPaymentsController@cancel');
Route::get('account/tax-receipts', 'TaxReceiptController@index');
Route::get('account/tax-receipts/{id}', 'TaxReceiptController@pdf');
Route::get('account/fundraisers', 'FundraisingPagesController@list');
Route::get('account/memberships', 'MembershipsController@index');
Route::get('account/purchased-media', 'PurchasedMediaController@index');
Route::get('account/purchased-media/{id}', 'PurchasedMediaController@view');
Route::get('account/impact', 'GivingImpactController@index');

Route::get('cart', 'CartsController@viewCart')->name('frontend.carts.view_cart');
Route::get('cart/currency/{currency}', 'CartsController@switchCurrency')->name('frontend.carts.switch_currency');
Route::any('carts/{cart}/tokenize/return', 'CartsController@tokenizeReturn');
Route::any('carts/{cart}/tokenize/cancel', 'CartsController@tokenizeCancel');

Route::get('feed', 'FeedsController@index');
Route::get('feed.php', 'FeedsController@index');

Route::get('contributions/{name}', 'OrdersController@show')->name('order_review');
Route::get('contributions/{name}/thank-you', 'OrdersController@thankYou')->name('frontend.orders.thank_you');

Route::get('pledge/{name}/thank-you', 'PledgesController@thankYou');

Route::get('search/{terms?}', 'SearchController@results')->name('frontend.search.results');

Route::any('products/search/{terms?}', 'ProductsController@search')->name('frontend.products.search.results');
Route::any('products/search.php', 'ProductsController@search');
Route::any('products/search', 'ProductsController@search');

Route::post('ds/form/verify_captcha', 'FormsController@verifyCaptcha');
Route::post('ds/form/submit_to_email', 'FormsController@submitToEmail');
Route::post('ds/form/signup', 'FormsController@signup');

Route::get('ds/file', 'DownloadsController@product');
Route::get('downloads/{filename}', 'DownloadsController@show');

Route::get('fundraisers', 'FundraisingPagesController@list_all')->name('frontend.fundraising_pages.list_all');
Route::get('fundraisers/create', 'FundraisingPagesController@create')->name('frontend.fundraising_pages.create');
Route::post('fundraisers/insert', 'FundraisingPagesController@insert')->name('frontend.fundraising_pages.insert');
Route::get('fundraisers/{page_url}/edit', 'FundraisingPagesController@edit');
Route::post('fundraisers/{page_url}/report', 'FundraisingPagesController@report');
Route::post('fundraisers/{page_url}/cancel', 'FundraisingPagesController@cancel');
Route::post('fundraisers/{page_url}/update', 'FundraisingPagesController@update')->name('frontend.fundraising_pages.update');
Route::get('fundraisers/{page_url}', 'FundraisingPagesController@view')->name('frontend.fundraising_pages.view');

Route::get('sms/{payload}', 'SMSController@handlePayload')->name('sms');

Route::get('sponsorship', 'SponsorshipsController@index');
Route::get('sponsorship/index.php', 'SponsorshipsController@index');
Route::get('sponsorship/{id}', 'SponsorshipsController@show');

Route::get('tax_receipt/{id}/pdf', 'TaxReceiptController@pdf');
Route::get('tributes/{id}/pdf', 'OrdersController@tributePdf');

Route::get('robots.txt', 'SitemapsController@robots');
Route::get('sitemap.xml', 'SitemapsController@index');

Route::get('fundraising/forms/{code}', 'DonationFormsController')->name('donation-forms.show');
Route::get('fundraising/forms/{code}/qr-code', 'DonationFormsQRCodeController')->name('donation-forms.qr-code');
Route::any('fundraising/p2p/donate/{code}', 'PeerToPeerController@getDonationForm')->name('peer-to-peer-campaign.donate');
Route::any('fundraising/p2p/{code}/join-team/{join_code?}', 'PeerToPeerController@getFundraisingForm')->name('peer-to-peer-campaign.join-team');
Route::any('fundraising/p2p/{code}/{path?}', 'PeerToPeerController@getFundraisingForm')->where('path', '.+')->name('peer-to-peer-campaign.spa');
Route::get('fundraising/p2p', 'PeerToPeerController');

Route::get('/v1/widgets/{widgetId}', 'WidgetsController@get')->name('widgets.get');
Route::get('/embed/donation/{code}', 'EmbeddableDonationFormController@index')->name('embeddable.donation');
Route::get('/virtual-event/{code}', 'VirtualEventController@index')->name('virtualevent');
Route::get('/virtual-event/{code}/dashboard', 'VirtualEventDashboardController@index')->name('virtualevent.dashboard');
Route::get('/virtual-event/{pledge_campaign}/send-reaction', 'VirtualEventReactionController@store')->name('virtualevent.send_reaction');

// legacy order routes
Route::permanentRedirect('order/{name}', '/contributions/{name}');
Route::permanentRedirect('order/{name}/thank-you', '/contributions/{name}/thank-you');

// See: https://github.com/funkjedi/donorshops/issues/15
Route::any('{path}', 'DefaultController@handlePath')->where('path', '.+');
