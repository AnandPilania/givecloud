<?php

use Illuminate\Support\Facades\Route;

Route::apiResource('resthook_subscriptions', 'ResthookSubscriptionsController');

Route::get('check', 'AuthController@show')->name('zapier.auth.show');
Route::get('supporters', 'AccountsController@index')->name('zapier.supporters.index');
Route::get('contributions', 'ContributionController@index')->name('zapier.contributions.index');
