<?php

use Illuminate\Support\Facades\Route;

Route::get('feeds/{postType}/posts', 'PostController@index')->name('feeds.posts.index');
Route::get('feeds/{postType}/posts/{post:id}', 'PostController@show')->name('feeds.posts.show');

Route::apiResource('supporters', 'AccountController')->only('index', 'show');
Route::apiResource('contributions', 'ContributionController')->only('index', 'show');
Route::apiResource('products', 'ProductController')->only('index', 'show');
Route::post('variants/{variantHashId}/inventory', 'InventoryController@store')->name('variants.inventory.store');
Route::apiResource('user-defined-fields', 'UserDefinedFieldController')->names('user_defined_fields'); // correct route naming.
