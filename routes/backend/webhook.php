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

Route::get('.well-known/apple-developer-merchantid-domain-association', 'ApplePayController');

Route::post('webhook/messenger', 'WebhookController@postBotMan')->name('webhook.messenger');
Route::post('webhook/authorizenet', 'WebhookController@postAuthorizeNet')->name('webhook.authorizenet');
Route::post('webhook/paypal', 'WebhookController@postPaypal')->name('webhook.paypal');
Route::post('webhook/stripe', 'WebhookController@postStripe')->name('webhook.stripe');
Route::post('webhook/mux', 'WebhookController@postMux')->name('webhook.mux');

Route::post('wc-api/WC_Gateway_Paypal', 'WebhookController@postPaypal');
