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

Route::get('profile', 'UserController@profile')->name('backend.profile');
Route::put('profile/notifications', 'UserController@notifications')->name('backend.profile.notifications');

Route::get('auth/2fa-nagger', 'AuthController@twoFactorNagger')->name('backend.auth.2fa_nagger');
Route::get('auth/canny', 'CannyAuthController')->name('backend.auth.canny');

// Temporary, can be removed when public-services PR is merged and deployed: https://github.com/givecloud/public-services/pull/4
Route::get('auth/social/callback', 'SocialiteController@callback')->withoutMiddleware(['auth']);

Route::get('socialite/redirect/{provider}', 'SocialiteController@redirect')->withoutMiddleware(['auth'])->name('backend.socialite.redirect');
Route::get('socialite/callback', 'SocialiteController@callback')->withoutMiddleware(['auth'])->name('backend.socialite.callback');
Route::get('socialite/confirm/{provider}/{token}', 'SocialiteController@confirm')->withoutMiddleware(['auth'])->name('backend.socialite.confirm');
Route::get('socialite/revoke/{provider}', 'SocialiteController@revoke')->name('backend.socialite.revoke');

Route::post('personal-access-tokens', 'PersonalAccessTokenController@store')->name('backend.personal_access_tokens.store');
Route::post('pinned-menu-items', 'UserPinnedMenuItemsController')->name('backend.pin_menu_items.store');
Route::delete('personal-access-tokens/{token}', 'PersonalAccessTokenController@destroy')->name('backend.personal_access_tokens.destroy');

Route::get('invoice/{invoice_id}', 'SessionController@invoice');

Route::get('supporter_types/{id}/edit', 'AccountTypesController@edit')->name('backend.supporter_types.edit');
Route::post('supporter_types/{id}/update', 'AccountTypesController@update')->name('backend.supporter_types.update');
Route::get('supporter_types/{id}/destroy', 'AccountTypesController@destroy')->name('backend.supporter_types.destroy');
Route::get('supporter_types/add', 'AccountTypesController@add')->name('backend.supporter_types.add');
Route::post('supporter_types/new', 'AccountTypesController@store')->name('backend.supporter_types.store');

Route::get('aliases', 'AliasController@index')->name('backend.alias.index');
Route::get('aliases/add', 'AliasController@add');
Route::get('aliases/{alias_id}/edit', 'AliasController@edit');
Route::post('aliases/add', 'AliasController@insert');
Route::post('aliases/{alias_id}/edit', 'AliasController@update');
Route::post('aliases/{alias_id}/destroy', 'AliasController@destroy');

Route::post('api/v1/tinymce/imageupload', 'ImageController@imageUpload');
Route::get('api/v1/tinymce/imagetools', 'ImageController@imageTools');

Route::get('check-ins', 'CheckInController@dash');
Route::get('check-ins/search/{keywords}', 'CheckInController@search');

Route::get('chargebee/updated', 'SessionController@chargebeeUpdated');

Route::get('design', 'ThemeController@index');
Route::get('design/customize', 'BucketController@index')->name('backend.bucket.index');
Route::get('design/customize/add', 'BucketController@view');
Route::post('design/customize/destroy', 'BucketController@destroy');
Route::get('design/customize/edit', 'BucketController@view');
Route::post('design/customize/insert', 'BucketController@insert');
Route::post('design/customize/save', 'BucketController@save');
Route::post('design/customize/update', 'BucketController@update');

Route::get('themes/{theme}/activate', 'ThemeController@activate');
Route::get('themes/{theme}/lock', 'ThemeController@lock');
Route::get('themes/{theme}/unlock', 'ThemeController@unlock');
Route::get('themes/{theme}/editor', 'ThemeController@editor');
Route::get('themes/{theme}/editor/assets/{asset}.json', 'ThemeController@getAsset');
Route::post('themes/{theme}/editor/assets/{asset}', 'ThemeController@saveAsset');
Route::get('themes/{theme}-latest.zip', 'ThemeController@downloadLatestTheme');
Route::get('themes/{theme}.zip', 'ThemeController@downloadTheme');

Route::post('donors/gift', 'DonorController@gift');
Route::post('donors/view', 'DonorController@view');
Route::get('donors/codes/clearCache', 'DonorController@clearCache');
Route::get('donors/{donor_id}.json', 'DonorController@getDonor');

Route::get('downloads.json', 'DownloadController@getFiles');
Route::get('downloads/{file}', 'DownloadController@download');
Route::delete('downloads/{file}', 'DownloadController@destroy');
Route::post('downloads/cdn/sign', 'DownloadController@cdnSign');
Route::post('downloads/cdn/done', 'DownloadController@cdnDone');

Route::get('emails', 'EmailController@index');
Route::post('emails/destroy', 'EmailController@destroy');
Route::post('emails/save', 'EmailController@save');
Route::get('emails/add', 'EmailController@view')->name('backend.emails.add');
Route::get('emails/edit', 'EmailController@view');

Route::get('feeds', 'FeedController@index')->name('backend.feeds.index');
Route::post('feeds/destroy', 'FeedController@destroy')->name('backend.feeds.destroy');
Route::post('feeds/insert', 'FeedController@insert');
Route::post('feeds/update', 'FeedController@update')->name('backend.feeds.update');
Route::get('feeds/add', 'FeedController@view')->name('backend.feeds.add');
Route::get('feeds/edit', 'FeedController@view')->name('backend.feeds.edit');
Route::get('feeds/posts', 'PostController@index')->name('backend.post.index');
Route::post('feeds/posts.ajax', 'PostController@index_ajax');
Route::post('feeds/posts/destroy', 'PostController@destroy');
Route::post('feeds/posts/insert', 'PostController@insert');
Route::post('feeds/posts/sequence', 'PostController@sequence');
Route::post('feeds/posts/update', 'PostController@update')->name('backend.posts.update');
Route::get('feeds/posts/add', 'PostController@view');
Route::get('feeds/posts/edit', 'PostController@view')->name('backend.posts.edit');
Route::get('feeds/posts/{id}/duplicate', 'PostController@duplicate');

Route::get('fundraising-pages', 'FundraisingPagesController@index')->name('backend.fundraising_pages.index');
Route::get('fundraising-pages.csv', 'FundraisingPagesController@index_csv');
Route::post('fundraising-pages.json', 'FundraisingPagesController@index_json')->name('backend.fundraising-pages.index_json');
Route::get('fundraising-pages/{id}', 'FundraisingPagesController@view')->name('backend.fundraising-pages.view');
Route::post('fundraising-pages/{id}/update', 'FundraisingPagesController@update');
Route::get('fundraising-pages/{id}/suspend', 'FundraisingPagesController@suspend');
Route::get('fundraising-pages/{id}/destroy', 'FundraisingPagesController@destroy');
Route::get('fundraising-pages/{id}/restore', 'FundraisingPagesController@restore');
Route::get('fundraising-pages/{id}/activate', 'FundraisingPagesController@activate');
Route::post('fundraising-pages/{id}/contributions.json', 'FundraisingPagesController@orders_json')->name('backend.fundraising_pages/orders_json');
Route::get('fundraising-pages/{id}/contributions.csv', 'FundraisingPagesController@orders_csv')->name('backend.fundraising_pages/orders_csv');

Route::get('images/directory_listing', 'ImageController@directory_listing');
Route::post('images/destroy', 'ImageController@destroy');

Route::get('imports', 'ImportsController@index')->name('backend.imports.index');
Route::get('imports/create', 'ImportsController@create')->name('backend.imports.create');
Route::post('imports', 'ImportsController@store')->name('backend.imports.store');
Route::get('import/{id}/download', 'ImportsController@download')->name('backend.imports.download');
Route::get('imports/wizard/{import}/{id?}', 'SPAController')->name('backend.imports.show');

Route::get('import', 'ImportController@index')->name('backend.import');
Route::get('import/history', 'ImportController@history');
Route::get('import/templates/{type}', 'ImportController@template');
Route::get('import/template/{name}', 'ImportTemplateController')->name('backend.import.template.download');

Route::post('import/upload', 'ImportController@upload');
Route::get('import/{id}', 'ImportController@monitor');
Route::get('import/{id}/file', 'ImportController@downloadFile');
Route::get('import/{id}/import-messages', 'ImportController@importMessages');
Route::get('import/{id}/analysis-messages', 'ImportController@analysisMessages');
Route::get('import/{id}/abort', 'ImportController@abort');
Route::get('import/{id}/start-import', 'ImportController@startImport');

Route::get('import/sponsee-photos', 'ImportSponseePhotosController@index')->name('backend.import_sponsee_photos.index');
Route::post('import/sponsee-photos/check', 'ImportSponseePhotosController@checkForSponseeMatch')->name('backend.import_sponsee_photos.check_for_sponsee_match');
Route::post('import/sponsee-photos/sign', 'ImportSponseePhotosController@signUploadUrl')->name('backend.import_sponsee_photos.sign_upload_url');
Route::post('import/sponsee-photos/attach', 'ImportSponseePhotosController@attachPhotoToSponsee')->name('backend.import_sponsee_photos.attach_photo_to_sponsee');

Route::get('kiosks', 'KioskController@showKiosks')->name('backend.kiosks.index');
Route::get('kiosks/{kiosk}', 'KioskController@showKiosk');

Route::post('media/list', 'MediaController@list');
Route::post('media/cdn/sign', 'MediaController@cdnSign')->name('backend.media.cdn_sign');
Route::post('media/cdn/done', 'MediaController@cdnDone')->name('backend.media.cdn_done');
Route::post('media/{id}/destroy', 'MediaController@destroy');

Route::get('supporters', 'MemberController@index')->name('backend.member.index');
Route::post('supporters.listing', 'MemberController@listing')->name('backend.member.listing');
Route::post('supporters/destroy', 'MemberController@destroy');
Route::get('supporters/batch', 'MemberController@batch')->name('backend.member.batch');
Route::get('supporters/export/emails', 'MemberController@export_emails')->name('backend.member.export_emails');
Route::get('supporters/export/all', 'MemberController@export')->name('backend.member.export');
Route::post('supporters/save', 'MemberController@save')->name('backend.member.save');
Route::get('supporters/add', 'MemberController@view')->name('backend.member.add');
Route::get('supporters/edit', 'MemberController@view')->name('backend.member.view');
Route::get('supporters/{id}/edit', 'MemberController@view')->name('backend.member.edit');
Route::post('supporters/{id}/merge', 'MemberController@merge')->name('backend.member.merge');

Route::get('supporter-verification-status/{member}/verify', 'MemberVerifiedStatusController@store')->name('backend.supporter_verification.verify');
Route::get('supporter-verification-status/{member}/deny', 'MemberVerifiedStatusController@destroy')->name('backend.supporter_verification.deny');

Route::get('group_accounts/{id}/modal', 'GroupAccountController@modal')->name('backend.group_accounts.modal');
Route::get('group_accounts/add/modal', 'GroupAccountController@modal')->name('backend.group_accounts.modal_add');
Route::post('group_accounts/insert', 'GroupAccountController@insert');
Route::post('group_accounts/update', 'GroupAccountController@update');
Route::post('group_accounts/destroy', 'GroupAccountController@destroy')->name('backend.group_account.destroy');

Route::get('memberships', 'MembershipController@index')->name('backend.memberships.index');
Route::post('memberships/destroy', 'MembershipController@destroy');
Route::post('memberships/save', 'MembershipController@save');
Route::get('memberships/add', 'MembershipController@view');
Route::get('memberships/edit', 'MembershipController@view')->name('backend.memberships.edit');

Route::get('messenger', 'MessengerController@showMessenger');
Route::get('messenger/console', 'MessengerController@showConsole');
Route::get('messenger/conversations', 'ConversationController@showConversations')->name('backend.messenger.conversations');
Route::post('messenger/conversations', 'ConversationController@saveConversation');
Route::get('messenger/conversations/add', 'ConversationController@addConversation');
Route::get('messenger/conversations/{conversation}', 'ConversationController@showConversation');

Route::get('virtual-events', 'VirtualEventsController@index')->name('backend.virtual-events.index');
Route::post('virtual-events.ajax', 'VirtualEventsController@index_ajax');
Route::get('virtual-events/create', 'VirtualEventsController@edit');
Route::post('virtual-events/save', 'VirtualEventsController@save');
Route::get('virtual-events/{virtualEventId}/edit', 'VirtualEventsController@edit');
Route::post('virtual-events/{virtualEventId}/update_slug', 'VirtualEventsController@updateSlug');
Route::post('virtual-events/{virtualEventId}/destroy', 'VirtualEventsController@destroy');

// Orders + Transactions
Route::get('contributions-v2', 'ContributionController@index')->name('backend.contributions.index');
Route::post('contributions-v2.listing', 'ContributionController@listing')->name('backend.contributions.listing');

Route::get('contributions', 'OrderController@index')->name('backend.orders.index');
Route::post('contributions.listing', 'OrderController@listing')->name('backend.orders.listing');
Route::get('contributions.csv', 'OrderController@index_csv')->name('backend.orders.index_csv');
Route::get('contributions_with_items.csv', 'OrderController@orders_with_items_csv')->name('backend.orders.orders_with_items_csv');
Route::post('contributions/update', 'OrderController@update')->name('backend.orders.update');
Route::get('contributions/add', 'OrderController@view');
Route::get('contributions/edit', 'OrderController@view')->name('backend.orders.edit_without_id');
Route::get('contributions/{id}/edit', 'OrderController@view')->name('backend.orders.edit');
Route::get('contributions/set_vault', 'OrderController@set_vault')->name('backend.orders.set_vault');
Route::get('contributions/notify_site_owner', 'OrderController@notify_site_owner')->name('backend.orders.notify_site_owner');
Route::get('contributions/push_to_dpo', 'OrderController@push_to_dpo')->name('backend.orders.push_to_dpo');
Route::get('contributions/{id}/reprocess_downloads', 'OrderController@reprocess_downloads')->name('backend.orders.reprocess_downloads');
Route::get('contributions/reprocess_product_specific_emails', 'OrderController@reprocess_product_specific_emails')->name('backend.orders.reprocess_product_specific_emails');
Route::get('contributions/abandoned_carts', 'OrderController@abandoned_carts')->name('backend.orders.abandoned_carts');
Route::post('contributions/abandoned_carts.ajax', 'OrderController@abandoned_carts_ajax');
Route::get('contributions/abandoned_carts.csv', 'OrderController@abandoned_carts_csv')->name('backend.orders.abandoned_carts_csv');
Route::get('contributions/batch', 'OrderController@batch')->name('backend.orders.batch');
Route::get('contributions/packing_slip', 'OrderController@packing_slip')->name('backend.orders.packing_slip');
Route::get('contributions/custom_fields.csv', 'OrderController@export_custom_fields_csv');
Route::match(['get', 'post'], 'contributions/checkin', 'OrderController@checkin')->name('backend.orders.checkin');
Route::get('contributions/{order_id}/generate_tax_receipt', 'OrderController@generateTaxReceipt')->name('backend.orders.generate_tax_receipt');
Route::post('contributions/{id}/refund', 'OrderController@refund')->name('backend.orders.refund');

Route::get('contributions/number/{invoice_number}', 'OrderController@orderNumber')->name('backend.orders.order_number');
Route::get('contributions/{id}/linkMember', 'OrderController@linkMember')->name('backend.orders.link_member');
Route::get('contributions/{id}/createMember', 'OrderController@createMember')->name('backend.orders.create_member');
Route::get('contributions/{id}/unlinkMember', 'OrderController@unlinkMember');
Route::post('contributions/{id}/editDPData', 'OrderController@editDPData')->name('backend.orders.editDPData');
Route::post('contributions/{id}/editItem', 'OrderController@editItem')->name('backend.orders.editItem');
Route::post('contributions/{id}/editItemFields', 'OrderController@editItemFields')->name('backend.orders.editItemFields');
Route::post('contributions/{id}/editGiftAidEligibility', 'OrderController@editGiftAidEligibility')->name('backend.orders.editGiftAidEligibility');
Route::get('contributions/{id}/getItemFields', 'OrderController@getItemFields')->name('backend.orders.getItemFields');
Route::post('contributions/{id}/destroy', 'OrderController@destroy')->name('backend.orders.destroy');
Route::get('contributions/{id}/refresh-payment-status', 'OrderController@refreshLatestPaymentStatus')->name('backend.orders.refresh-payment-status');
Route::post('contributions/{id}/spam', 'OrderController@markAsSpam')->name('backend.orders.spam');
Route::get('contributions/{id}/restore', 'OrderController@restore')->name('backend.orders.restore');
Route::get('contributions/{id}/complete', 'OrderController@complete')->name('backend.orders.complete');
Route::get('contributions/{id}/incomplete', 'OrderController@incomplete')->name('backend.orders.incomplete');
Route::get('order_items/{id}/applyGroup', 'OrderController@item_applyGroup')->name('backend.orders.applyGroup');

Route::get('pages', 'PageController@index')->name('backend.page.index');
Route::post('pages/destroy', 'PageController@destroy');
Route::post('pages/insert', 'PageController@insert');
Route::post('pages/autosave', 'PageController@autosave');
Route::post('pages/update', 'PageController@update');
Route::get('pages/add', 'PageController@view');
Route::post('pages/quick-add', 'PageController@quickAdd');
Route::get('pages/edit', 'PageController@view')->name('backend.page.edit');
Route::get('pages/copy', 'PageController@copy');
Route::get('pages/add/categories', 'PageController@addAllCategories');

Route::get('pledges', 'PledgesController@index')->name('backend.pledges.index');
Route::post('pledges.json', 'PledgesController@index_json');
Route::get('pledges.csv', 'PledgesController@index_csv');
Route::get('pledges/campaigns', 'PledgeCampaignsController@index')->name('backend.campaign.index');
Route::post('pledges/campaigns.json', 'PledgeCampaignsController@index_json')->name('backend.campaign.index_json');
Route::get('pledges/campaigns/new/modal', 'PledgeCampaignsController@modal')->name('backend.campaign.modal_new');
Route::post('pledges/campaigns/insert', 'PledgeCampaignsController@insert')->name('backend.campaign.insert');
Route::get('pledges/campaigns/{id}/modal', 'PledgeCampaignsController@modal')->name('backend.campaign.modal_update');
Route::post('pledges/campaigns/{id}/update', 'PledgeCampaignsController@update')->name('backend.campaign.update');
Route::get('pledges/campaigns/{id}/destroy', 'PledgeCampaignsController@destroy')->name('backend.campaign.destroy');
Route::post('pledges/insert', 'PledgesController@insert')->name('backend.pledges.insert');
Route::get('pledges/new/modal', 'PledgesController@modal');
Route::get('pledges/{id}/modal', 'PledgesController@modal')->name('backend.pledges.modal');
Route::post('pledges/{id}/update', 'PledgesController@update');
Route::get('pledges/{id}/destroy', 'PledgesController@destroy');
Route::get('pledges/{id}/calculate', 'PledgesController@calculate');

Route::get('products', 'ProductController@index')->name('backend.products.index');
Route::post('products.ajax', 'ProductController@index_ajax')->name('backend.products.ajax');
Route::get('products.csv', 'ProductController@export')->name('backend.products.export');
Route::get('products/variants.csv', 'ProductController@exportVariants')->name('backend.product.variants.export');

Route::get('products/copy', 'ProductController@copy');
Route::post('products/destroy', 'ProductController@destroy');
Route::post('products/restore', 'ProductController@restore');
Route::post('products/save', 'ProductController@save')->name('backend.products.save');
Route::get('products/add', 'ProductController@view');
Route::get('products/edit', 'ProductController@view')->name('backend.products.edit');
Route::post('products/validate_sku', 'ProductController@validateSku');
Route::get('products/templates/{id}/create', 'ProductController@create_from_template');

Route::get('products/categories', 'ProductCategoryController@index')->name('backend.product_category.index');
Route::post('products/categories/destroy', 'ProductCategoryController@destroy');
Route::post('products/categories/save', 'ProductCategoryController@save');
Route::get('products/categories/add', 'ProductCategoryController@view');
Route::get('products/categories/edit', 'ProductCategoryController@view');

Route::get('promotions', 'PromotionController@index')->name('backend.promotions.index');
Route::post('promotions/destroy', 'PromotionController@destroy');
Route::post('promotions/save', 'PromotionController@save')->name('backend.promotions.save');
Route::get('promotions/add', 'PromotionController@view');
Route::get('promotions/edit', 'PromotionController@view');
Route::get('promotions/{id}/edit', 'PromotionController@view')->name('backend.promotions.edit');
Route::post('promotions/{id}/duplicate', 'PromotionController@duplicate')->name('backend.promotions.duplicate');
Route::get('promotions/{id}/calculate_usage', 'PromotionController@calculate_usage')->name('backend.promotions.calculate_usage');

/*
* Routes for /reports
*/
Route::group(['prefix' => 'reports'], function () {
    Route::get('check_ins', 'Reports\CheckInController@index')->name('backend.reports.check_ins.index');
    Route::get('check_ins.csv', 'Reports\CheckInController@export');
    Route::get('check_ins/audit', 'Reports\CheckInController@audit');
    Route::get('check_ins/audit.csv', 'Reports\CheckInController@audit_export');
    Route::get('customer', 'Reports\CustomerController@index');
    Route::get('inventory-export', 'Reports\InventoryController@index');
    Route::post('inventory-export.csv', 'Reports\InventoryController@export');
    Route::get('mature_sponsorships', 'Reports\MatureSponsorshipsController@index');
    Route::get('orders', 'Reports\OrderController@index');
    Route::get('payments', 'Reports\PaymentsController@index')->name('backend.reports.payments.index');
    Route::post('payments.ajax', 'Reports\PaymentsController@get')->name('backend.reports.payments.index_ajax');
    Route::post('payments-aggregate.ajax', 'Reports\PaymentsController@getAggregate')->name('backend.reports.payments.aggregate_ajax');
    Route::get('payments.csv', 'Reports\PaymentsController@export');
    Route::get('members', 'Reports\MembersController@index')->name('backend.reports.members.index');
    Route::post('members.ajax', 'Reports\MembersController@get')->name('backend.reports.members.ajax');
    Route::get('members.csv', 'Reports\MembersController@export')->name('backend.reports.members.export');
    Route::get('payments-old', 'Reports\OldPaymentsController@index');
    Route::get('payments-old.csv', 'Reports\OldPaymentsController@export');
    Route::get('payments-by-item', 'Reports\PaymentsByItemController@index')->name('backend.reports.payments-by-item.index');
    Route::post('payments-by-item.ajax', 'Reports\PaymentsByItemController@get');
    Route::get('payments-by-item.csv', 'Reports\PaymentsByItemController@export');
    Route::get('contribution-line-items', 'Reports\ContributionLineItemsController@index')->name('backend.reports.contribution-line-items.index');
    Route::post('contribution-line-items', 'Reports\ContributionLineItemsController@listing')->name('backend.reports.contribution-line-items.listing');
    Route::get('contribution-line-items.export', 'Reports\ContributionLineItemsController@export')->name('backend.reports.contribution-line-items.export');
    Route::get('payments-by-gl', 'Reports\PaymentsByGLCodeController@index');
    Route::get('pledge-campaigns', 'Reports\PledgeCampaignsController@index')->name('backend.reports.pledge-campaigns.index');
    Route::get('pledge-campaigns.csv', 'Reports\PledgeCampaignsController@export');
    Route::post('pledge-campaigns.json', 'Reports\PledgeCampaignsController@get');
    Route::get('products', 'Reports\ProductController@index')->name('backend.reports.contributions-by-product.index');
    Route::post('products.ajax', 'Reports\ProductController@get');
    Route::get('products.csv', 'Reports\ProductController@export');
    Route::get('referral_sources', 'Reports\ReferralSourceController@index')->name('backend.reports.referral_sources.index');
    Route::post('referral_sources.ajax', 'Reports\ReferralSourceController@get');
    Route::get('referral_sources.csv', 'Reports\ReferralSourceController@export');
    Route::get('settlements', 'Reports\SettlementController@index')->name('backend.reports.settlements.index');
    Route::post('settlements.ajax', 'Reports\SettlementController@get');
    Route::get('settlements.csv', 'Reports\SettlementController@export');
    Route::get('shipping', 'Reports\ShippingController@index')->name('backend.reports.shipping.index');
    Route::get('shipping.csv', 'Reports\ShippingController@export');
    Route::get('stock', 'Reports\StockController@index')->name('backend.reports.stock.index');
    Route::get('stock.csv', 'Reports\StockController@export');
    Route::get('tax', 'Reports\TaxController@index')->name('backend.reports.tax.index');
    Route::get('tax.csv', 'Reports\TaxController@export');

    Route::get('platform-fees', 'Reports\PlatformFeeController@index')->name('backend.reports.transaction_fees.index');
    Route::get('platform-fees.csv', 'Reports\PlatformFeeController@export');
    Route::post('platform-fees.json', 'Reports\PlatformFeeController@get');

    Route::get('transactions', 'Reports\TransactionController@index')->name('backend.reports.transactions.index');
    Route::post('transactions.ajax', 'Reports\TransactionController@get');
    Route::get('transactions.csv', 'Reports\TransactionController@export');
    Route::get('impact-by-supporter', 'Reports\ImpactByAccountController@index')->name('backend.reports.impact_by_supporter.index');
    Route::get('impact-by-supporter.csv', 'Reports\ImpactByAccountController@export')->name('backend.reports.impact_by_supporter.export');
    Route::post('impact-by-supporter.json', 'Reports\ImpactByAccountController@get');
    Route::get('donor-covers-costs', 'Reports\DonorCoversCostsController@index')->name('backend.reports.donor-covers-costs.index');
    Route::post('donor-covers-costs.json', 'Reports\DonorCoversCostsController@get');
    Route::get('donor-covers-costs.csv', 'Reports\DonorCoversCostsController@export');
    Route::get('transient-logs', 'Reports\TransientLogController@index')->name('backend.reports.transient_logs.index');
    Route::post('transient-logs.json', 'Reports\TransientLogController@get')->name('backend.reports.transient_logs.get');
    Route::get('transient-logs/{log}.json', 'Reports\TransientLogController@show')->name('backend.reports.transient_logs.show');
});

Route::get('sessions/login', 'SessionController@login')->name('backend.session.login');
Route::get('sessions/logout', 'SessionController@logout')->name('backend.session.logout');

/*
* Routes for /settings
*/
Route::group(['prefix' => 'settings'], function () {
    Route::get('home', 'SettingController@home')->name('backend.settings.home');
    Route::get('billing', 'BillingController@index')->name('backend.settings.billing');
    Route::post('billing/save', 'BillingController@save');
    Route::get('billing/customer_portal', 'BillingController@redirectToCustomerPortal');
    Route::get('billing/overdue/remind_later', 'BillingController@setOverdueReminder');
    Route::get('billing/overdue/already_paid', 'BillingController@markOverdueAsAlreadyPaid');
    Route::get('billing/overdue/not_me', 'BillingController@flagOtherUserForOverdueAmount');

    Route::post('billing/chargebee/checkout', 'Settings\Billing\ChargebeeSubscriptionController@createCustomerCheckout')->name('billing.chargebee.checkout');
    Route::get('billing/chargebee/callback', 'Settings\Billing\ChargebeeSubscriptionController@callback')->name('billing.chargebee.callback');

    Route::get('supporters', 'SettingController@accounts')->name('backend.settings.supporters');
    Route::post('supporters/save', 'SettingController@accounts_save')->name('backend.settings.supporters_save');
    Route::get('dcc', 'SettingController@dcc')->name('backend.settings.dcc');
    Route::post('dcc/save', 'SettingController@dcc_save');
    Route::get('dp', 'SettingController@dp');
    Route::post('dp/save', 'SettingController@dp_save');
    Route::post('dp/pull_data/donor', 'SettingController@dp_pull_donor_data');
    Route::get('fundraising-pages', 'SettingController@fundraisingPages')->name('backend.settings.fundraising_pages');
    Route::post('fundraising-pages/save', 'SettingController@fundraisingPages_save');
    Route::get('payments', 'SettingController@payments')->name('backend.settings.payments');
    Route::post('payments/save', 'SettingController@payments_save');
    Route::get('peer-to-peer', 'SettingController@peerToPeer');
    Route::post('peer-to-peer/save', 'SettingController@peerToPeerSave');
    Route::get('gift_aid', 'SettingController@giftAid')->name('backend.settings.gift_aid');
    Route::post('gift_aid/save', 'SettingController@giftAid_save');
    Route::get('infusionsoft', 'SettingController@infusionsoft');
    Route::post('infusionsoft/save', 'SettingController@infusionsoft_save');
    Route::get('infusionsoft/connect', 'SettingController@infusionsoft_connect');
    Route::get('infusionsoft/disconnect', 'SettingController@infusionsoft_disconnect');
    Route::post('infusionsoft/test', 'SettingController@infusionsoft_test');
    Route::get('general', 'SPAController')->name('backend.settings.general');
    Route::get('taxcloud', 'SettingController@taxcloud');
    Route::post('taxcloud/save', 'SettingController@taxcloud_save');
    Route::get('security', 'SecurityController@index')->name('backend.security.index');
    Route::post('security', 'SecurityController@save');
    Route::get('security/payments.json', 'SecurityController@getHistoricalChartData');
    Route::get('shipping', 'SettingController@shipping')->name('backend.settings.shipping');
    Route::post('shipping/save', 'SettingController@shipping_save');
    Route::get('shipstation', 'SettingController@shipstation');
    Route::post('shipstation/save', 'SettingController@shipstation_save');
    Route::get('email', 'SettingController@email')->name('backend.settings.email');
    Route::post('email/save', 'SettingController@email_save')->name('backend.settings.email_save');
    Route::get('sponsorship', 'SettingController@sponsorship')->name('backend.settings.sponsorship');
    Route::post('sponsorship/save', 'SettingController@sponsorship_save');
    Route::get('tax_receipts', 'SettingController@taxReceipts')->name('backend.settings.tax_receipts');
    Route::post('tax_receipts', 'SettingController@taxReceipts_save');
    Route::get('tax_receipts/templates/{id}', 'SettingController@taxReceiptTemplate');
    Route::post('tax_receipts/templates/{id}', 'SettingController@taxReceiptTemplate_save');
    Route::get('tax_receipts/templates/{id}/duplicate', 'SettingController@taxReceiptTemplate_duplicate');
    Route::get('tax_receipts/templates/{id}/preview', 'SettingController@taxReceiptTemplate_preview');
    Route::get('pos', 'SettingController@pos')->name('backend.settings.pos');
    Route::post('pos/save', 'SettingController@pos_save');
    Route::get('website', 'SettingController@website')->name('backend.settings.website');
    Route::post('website/save', 'SettingController@website_save');
    Route::get('sites', 'SettingController@sites');
    Route::post('sites/save', 'SettingController@sites_save');
    Route::get('zapier', 'Settings\\ZapierSettingsController@show')->name('backend.settings.zapier.show');
    Route::post('zapier', 'Settings\\ZapierSettingsController@store')->name('backend.settings.zapier.store');

    Route::get('payment', 'PaymentProviderController@showIndex')->name('backend.settings.payment');
    Route::post('payment', 'PaymentProviderController@storeProvider');
    Route::delete('payment', 'PaymentProviderController@deleteProvider');
    Route::patch('payment', 'PaymentProviderController@setDefaultProviders');
    Route::get('payment/authorizenet', 'PaymentProviderController@showAuthorizeNet');
    Route::get('payment/braintree', 'PaymentProviderController@showBraintree');
    Route::get('payment/caymangateway', 'PaymentProviderController@showCaymanGateway');
    Route::get('payment/givecloudtest', 'PaymentProviderController@showGivecloudTest');
    Route::get('payment/gocardless', 'PaymentProviderController@showGoCardless');
    Route::get('payment/gocardless/connect', 'PaymentProviderController@connectGoCardless');
    Route::get('payment/gocardless/disconnect', 'PaymentProviderController@disconnectGoCardless');
    Route::get('payment/gocardless/verify', 'PaymentProviderController@verifyGoCardless');
    Route::get('payment/nmi', 'PaymentProviderController@showNMI');
    Route::get('payment/paypal{type}', 'PaymentProviderController@showPayPal');
    Route::get('payment/paypal{type}/connect', 'PaymentProviderController@connectPayPal');
    Route::get('payment/paypal{type}/reconnect', 'PaymentProviderController@reconnectPayPal');
    Route::get('payment/paypal{type}/disconnect', 'PaymentProviderController@disconnectPayPal');
    Route::get('payment/paysafe', 'PaymentProviderController@showPaysafe');
    Route::get('payment/safesave', 'PaymentProviderController@showSafeSave');
    Route::get('payment/stripe', 'PaymentProviderController@showStripe');
    Route::get('payment/stripe/connect', 'PaymentProviderController@connectStripe');
    Route::get('payment/stripe/disconnect', 'PaymentProviderController@disconnectStripe');
    Route::get('payment/vanco', 'PaymentProviderController@showVanco');

    // hooks
    Route::get('hooks', 'HookController@index')->name('backend.settings.hooks.index');
    Route::get('hooks/create', 'HookController@create')->name('backend.settings.hooks.create');
    Route::post('hooks', 'HookController@store')->name('backend.settings.hooks.store');
    Route::get('hooks/{hook}', 'HookController@edit')->name('backend.settings.hooks.edit');
    Route::put('hooks/{hook}', 'HookController@update')->name('backend.settings.hooks.update');
    Route::delete('hooks/{hook}/destroy', 'HookController@destroy')->name('backend.settings.hooks.destroy');
    Route::get('hook-deliveries/{delivery}', 'HookDeliveryController@show')->name('backend.settings.hook_deliveries.show');
    Route::post('hook-deliveries/{delivery}/redeliver', 'HookRedeliveryController@store')->name('backend.settings.hook_redelivery.store');

    // integrations
    Route::get('integrations', 'SettingController@integrations')->name('backend.settings.integrations');

    // Salesforce integration
    Route::get('salesforce', 'Settings\SalesforceSettingsController@index')->name('backend.settings.integrations.salesforce.index');
    Route::post('salesforce', 'Settings\SalesforceSettingsController@store')->name('backend.settings.integrations.salesforce.store');
    Route::get('salesforce-legacy', 'Settings\SalesforceSettingsController@legacy')->name('backend.settings.integrations.salesforce.legacy');
    Route::get('salesforce-legacy/connect', 'Settings\SalesforceSettingsController@connect')->name('backend.settings.integrations.salesforce.connect');
    Route::get('salesforce-legacy/callback', 'Settings\SalesforceSettingsController@callback')->name('backend.settings.integrations.salesforce.callback');
    Route::get('salesforce-legacy/disconnect', 'Settings\SalesforceSettingsController@disconnect')->name('backend.settings.integrations.salesforce.disconnect');
    Route::get('salesforce-legacy/test', 'Settings\SalesforceSettingsController@test')->name('backend.settings.integrations.salesforce.test');

    // Double the donation
    Route::get('double-the-donation', 'Settings\DoubleTheDonationSettingsController@index')->name('backend.settings.integrations.double-the-donation.index');
    Route::post('double-the-donation', 'Settings\DoubleTheDonationSettingsController@store')->name('backend.settings.integrations.double-the-donation.store');
    Route::post('double-the-donation/test', 'Settings\DoubleTheDonationSettingsController@test')->name('backend.settings.integrations.double-the-donation.test');

    // Mailchimp
    Route::get('mailchimp', 'Settings\MailchimpSettingsController@index')->name('backend.settings.integrations.mailchimp.index');
    Route::post('mailchimp', 'Settings\MailchimpSettingsController@sync')->name('backend.settings.integrations.mailchimp.sync');

    // Hotglue - Zero Config
    Route::get('hotglue/{target}', 'Settings\HotglueZeroConfigSettingsController@index')->name('backend.settings.integrations.hotglue.index');

    // legacy
    Route::get('/', 'SettingController@index')->name('backend.settings.index');
    Route::post('save', 'SettingController@save');
});

Route::get('sponsors', 'SponsorController@index')->name('backend.sponsors.index');
Route::post('sponsors.ajax', 'SponsorController@index_ajax')->name('backend.sponsors.ajax');
Route::get('sponsors.csv', 'SponsorController@export');
Route::get('sponsors_detailed.csv', 'SponsorController@detailed_export');
Route::get('sponsors/sponsors_from_orders', 'SponsorController@sponsorsFromOrders');
Route::get('sponsor/add/{sponsorship_id}', 'SponsorController@add');
Route::get('sponsor/{sponsor_id}', 'SponsorController@view');
Route::delete('sponsor/{sponsor_id}', 'SponsorController@destroy');
Route::post('sponsor/{sponsor_id}', 'SponsorController@update');
Route::post('sponsor', 'SponsorController@store');

Route::get('sponsorship', 'SponsorshipController@index')->name('backend.sponsorship.index');
Route::post('sponsorship.ajax', 'SponsorshipController@index_ajax')->name('backend.sponsorship.ajax');
Route::get('sponsorship.csv', 'SponsorshipController@export');
Route::post('sponsorship/destroy', 'SponsorshipController@destroy');
Route::post('sponsorship/restore', 'SponsorshipController@restore');
Route::post('sponsorship/save', 'SponsorshipController@save')->name('backend.sponsorship.save');
Route::get('sponsorship/add', 'SponsorshipController@add');
Route::get('sponsorship/edit', 'SponsorshipController@view'); // <<< LEGACY
Route::get('sponsorship/{id}', 'SponsorshipController@view')->name('backend.sponsorship.view');

Route::get('sponsorship/segments', 'SegmentController@index')->name('backend.segment.index');
Route::post('sponsorship/segments/destroy', 'SegmentController@destroy')->name('backend.segment.destroy');
Route::post('sponsorship/segments/restore', 'SegmentController@restore');
Route::post('sponsorship/segments/save', 'SegmentController@save');
Route::get('sponsorship/segments/add', 'SegmentController@view');
Route::get('sponsorship/segments/edit', 'SegmentController@view');
Route::get('sponsorship/segments/items', 'SegmentItemsController@index');
Route::post('sponsorship/segments/items/destroy', 'SegmentItemsController@destroy');
Route::post('sponsorship/segments/items/restore', 'SegmentItemsController@restore');
Route::post('sponsorship/segments/items/save', 'SegmentItemsController@save');
Route::get('sponsorship/segments/items/add', 'SegmentItemsController@view');
Route::get('sponsorship/segments/items/edit', 'SegmentItemsController@view');

Route::get('sponsorship/payment_options', 'PaymentOptionsController@index')->name('backend.sponsorship.payment_options.index');
Route::post('sponsorship/payment_options/destroy', 'PaymentOptionsController@destroy')->name('backend.sponsorship.payment_options.destroy');
Route::post('sponsorship/payment_options/restore', 'PaymentOptionsController@restore');
Route::post('sponsorship/payment_options/save', 'PaymentOptionsController@save');
Route::get('sponsorship/payment_options/add', 'PaymentOptionsController@view');
Route::get('sponsorship/payment_options/edit', 'PaymentOptionsController@view');

Route::get('shipping', 'ShippingController@index');
Route::post('shipping/destroy', 'ShippingController@destroy');
Route::post('shipping/save', 'ShippingController@save');
Route::get('shipping/add', 'ShippingController@view');
Route::get('shipping/edit', 'ShippingController@view');
Route::post('shipping/tiers/destroy', 'ShippingTierController@destroy');
Route::post('shipping/tiers/save', 'ShippingTierController@save');
Route::get('shipping/tiers/add', 'ShippingTierController@view');
Route::get('shipping/tiers/edit', 'ShippingTierController@view');

Route::post('tax_receipt/{id}/modal', 'TaxReceiptsController@modal');
Route::get('tax_receipt/{id}/pdf', 'TaxReceiptsController@pdf')->name('backend.tax_receipts.pdf');
Route::post('tax_receipt/{id}/revise', 'TaxReceiptsController@revise');
Route::post('tax_receipt/{id}/issue', 'TaxReceiptsController@issue');
Route::post('tax_receipt/{id}/void', 'TaxReceiptsController@void');
Route::post('tax_receipt/{id}/notify', 'TaxReceiptsController@notify');
Route::get('tax_receipts', 'TaxReceiptsController@index')->name('backend.tax_receipts.index');
Route::post('tax_receipts.ajax', 'TaxReceiptsController@index_ajax');
Route::get('tax_receipts.csv', 'TaxReceiptsController@index_csv');
Route::get('tax_receipts/new', 'TaxReceiptsController@newReceipt')->name('backend.tax_receipts.new');
Route::post('tax_receipts/new', 'TaxReceiptsController@createReceipt');
Route::any('tax_receipts/receiptable.json', 'TaxReceiptsController@receiptable');
Route::get('tax_receipts/consolidated-receipting', 'TaxReceiptsController@consolidated');
Route::post('tax_receipts/consolidated-receipting', 'TaxReceiptsController@batchCreateReceipts');
Route::post('tax_receipts/bulk', 'TaxReceiptsController@bulkAction');

Route::get('tributes', 'TributesController@index')->name('backend.tributes.index');
Route::post('tributes.ajax', 'TributesController@index_ajax');
Route::get('tributes.csv', 'TributesController@index_csv');
Route::get('tributes_labels.pdf', 'TributesController@index_labels');
Route::post('tributes/{id}/modal', 'TributesController@modal');
Route::get('tributes/{id}/pdf', 'TributesController@pdf');
Route::post('tributes/{id}/destroy', 'TributesController@destroy');
Route::post('tributes/{id}/notify', 'TributesController@notify');
Route::post('tributes/{id}/edit', 'TributesController@edit');
Route::get('tributes/printUnsentLetters', 'TributesController@printUnsentLetters');
Route::get('tributes/sendUnsentLetters', 'TributesController@sendUnsentLetters');

Route::get('tribute_types', 'TributeTypesController@index')->name('backend.tribute_types.index');
Route::get('tribute_types/{id}/edit', 'TributeTypesController@edit');
Route::post('tribute_types/{id}/update', 'TributeTypesController@update');
Route::get('tribute_types/{id}/delete', 'TributeTypesController@delete');
Route::get('tribute_types/add', 'TributeTypesController@add');
Route::post('tribute_types/new', 'TributeTypesController@store');

Route::get('taxes', 'TaxController@index')->name('backend.taxes.index');
Route::post('taxes/destroy', 'TaxController@destroy');
Route::post('taxes/save', 'TaxController@save');
Route::get('taxes/add', 'TaxController@view');
Route::get('taxes/edit', 'TaxController@view');

Route::get('users', 'UserController@index')->name('backend.users.index');
Route::get('users.csv', 'UserController@emails');
Route::post('users/destroy', 'UserController@destroy');
Route::post('users/insert', 'UserController@insert')->name('backend.users.insert');
Route::post('users/update', 'UserController@update');
Route::get('users/add', 'UserController@view')->name('backend.users.add');
Route::get('users/edit', 'UserController@view')->name('backend.users.edit');
Route::post('users/{id}/reset', 'UserController@sendResetLinkEmail');
Route::post('users/{id}/regenerate-key', 'UserController@regenerateKey');
Route::delete('users/{id}/two-factor-authentication', 'UserController@disableTwoFactorAuthentication')->name('backend.users.disable-two-factor-authentication');

Route::get('utilities', 'UtilityController@view')->name('backend.utilities');
Route::get('utilities/dp_gifts_missing_pay_methods', 'UtilityController@dp_gifts_missing_pay_methods');
Route::get('utilities/sync_unsynced_orders', 'UtilityController@sync_unsynced_orders');
Route::get('utilities/sync_unsynced_txns', 'UtilityController@sync_unsynced_txns');
Route::get('utilities/importable_pledges', 'UtilityController@importable_pledges');
Route::get('utilities/all_pledges', 'UtilityController@all_pledges');
Route::get('utilities/show_unreceipted', 'UtilityController@show_unreceipted');
Route::get('utilities/process_receipts', 'UtilityController@process_receipts');
Route::get('utilities/orders_without_adjustments', 'UtilityController@orders_without_adjustments');
Route::get('utilities/media_force_download', 'Utilities\MediaForceDownloadController@index')->name('backend.utilities.media_force_download.index');
Route::post('utilities/media_force_download/autocomplete', 'Utilities\MediaForceDownloadController@autocomplete')->name('backend.utilities.media_force_download.autocomplete');
Route::post('utilities/media_force_download', 'Utilities\MediaForceDownloadController@update')->name('backend.utilities.media_force_download.update');
Route::get('utilities/preview_daily_digest', 'UtilityController@preview_daily_digest');

Route::post('unlock_site', 'SessionController@unlock_site')->name('backend.session.unlock_site');

Route::get('donor/codes/{code_type}.json', 'DonorController@getCodes');
Route::get('donor/codes/clearCache', 'DonorController@clearCache');
Route::post('donor/verify_connection.json', 'DonorController@verifyConnection');
Route::post('donors/import', 'DonorController@import');
Route::get('urls.json', 'SessionController@getUrls');
Route::get('products.json', 'SessionController@getProducts');
Route::get('variants.json', 'SessionController@getVariants');
Route::get('supporters.json', 'MemberController@autocomplete')->name('backend.members.autocomplete');
Route::get('promocodes.json', 'PromotionController@autocomplete');
Route::get('downloads.json', 'DownloadController@autocomplete');
Route::get('fundraisers.json', 'FundraisingPagesController@autocomplete');

Route::get('paypal/verify_connection.json', 'PayPalController@verifyConnection');
Route::get('paypal/verify_reference_transactions.json', 'PayPalController@verifyReferenceTransactions');

Route::get('recurring_payments', 'RecurringPaymentsController@index')->name('backend.recurring_payments.index');
Route::post('recurring_payments.ajax', 'RecurringPaymentsController@index_ajax');
Route::get('recurring_payments.csv', 'RecurringPaymentsController@index_csv');
Route::get('recurring_payments/{profile_id}', 'RecurringPaymentsController@show')->name('backend.recurring_payments.show');
Route::get('recurring_payments/{profile_id}/edit', 'RecurringPaymentsController@edit');
Route::post('recurring_payments/{profile_id}/edit', 'RecurringPaymentsController@saveEdits')->name('backend.recurring_payments.profile.edit');
Route::post('recurring_payments/{profile_id}/cancel', 'RecurringPaymentsController@processCancellation');
Route::post('recurring_payments/{profile_id}/update_cancel', 'RecurringPaymentsController@updateCancelReason')->name('backend.recurring_payments.profile.update_cancel');
Route::post('recurring_payments/{profile_id}/override_pledge', 'RecurringPaymentsController@overridePledgeId');
Route::get('recurring_payments/{profile_id}/enable', 'RecurringPaymentsController@enable');
Route::get('recurring_payments/{profile_id}/suspend', 'RecurringPaymentsController@suspend');
Route::post('recurring_payments/{profile_id}/charge', 'RecurringPaymentsController@charge');

Route::post('transactions/{id}/modal', 'TransactionsController@modal');
Route::post('transactions/{id}/issue_tax_receipt', 'TransactionsController@issueTaxReceipt');
Route::post('transactions/{id}/sync_dpo', 'TransactionsController@syncDpo');
Route::post('transactions/{id}/refund', 'TransactionsController@refund');
Route::post('transactions/{id}/refresh-payment-status', 'TransactionsController@refreshPaymentStatus')->name('backend.transactions.refresh-payment-status');

Route::get('timeline/{type}/{id}', 'TimelineController@all');
Route::get('timeline/{timeline}', 'TimelineController@show');
Route::get('timeline/{timeline}/media.json', 'TimelineController@media');
Route::post('timeline/{timeline}', 'TimelineController@update');
Route::post('timeline', 'TimelineController@store');
Route::delete('timeline/{timeline}', 'TimelineController@destroy');

Route::get('products/{id}/contributions', 'Reports\ProductOrderController@index')->name('backend.reports.products.index');
Route::post('products/{id}/contributions.ajax', 'Reports\ProductOrderController@get')->name('backend.reports.products.get');
Route::get('products/{id}/contributions.csv', 'Reports\ProductOrderController@export')->name('backend.reports.products.export');
Route::get('products/{id}/contributions-with-items.csv', 'Reports\ProductOrderController@export_with_items')->name('backend.reports.products.export_with_items');

Route::get('supporters/{id}/login', 'MemberController@loginAs')->name('backend.members.login');
Route::get('supporters/{id}/restore', 'MemberController@restore')->name('backend.members.restore');
Route::post('supporters/{id}/payment_methods/import_from_vault', 'MemberController@import_payment_method_from_vault')->name('backend.members.import_payment_method_from_vault');

Route::get('pos', 'POSController@index')->name('backend.pos.index');
Route::post('pos/products.json', 'POSController@searchProducts');
Route::post('pos/categories.json', 'POSController@listCategories');
Route::post('pos/new', 'POSController@newOrder')->name('backend.pos.new');
Route::post('pos/bookmark/add', 'POSController@addBookmark');
Route::post('pos/bookmark/remove', 'POSController@removeBookmark');
Route::post('pos/{order}/add', 'POSController@addItem');
Route::post('pos/{order}/remove', 'POSController@removeItem');
Route::post('pos/{order}/update', 'POSController@updateOrder')->name('backend.pos.update');
Route::post('pos/{order}/promos/apply', 'POSController@applyPromos');
Route::post('pos/{order}/promos/remove', 'POSController@removePromos');
Route::post('pos/{order}/complete', 'POSController@completeOrder');
Route::post('pos/{order}/add_by_child_reference', 'POSController@addByChildReference');
Route::post('pos/{order}/add_by_fundraising_page', 'POSController@addByFundraisingPage');

// onboarding
Route::get('onboard/start', 'OnboardingController@start');
Route::post('onboard/finish', 'OnboardingController@finish');

// ExpressSetup
Route::get('express-setup', 'ExpressSetupController');

Route::get('onboard/nmi-setup/{token}', 'OnboardingController@getNmiSetup')->name('backend.onboarding.get_nmi_setup');
Route::post('onboard/nmi-setup/{token}', 'OnboardingController@postNmiSetup')->name('backend.onboarding.post_nmi_setup');

Route::get('fundraise', 'SPAController')->name('backend.fundraise.splash');
Route::get('fundraising/forms', 'SPAController')->name('backend.fundraising.forms');
Route::get('fundraising/forms/{form}', 'SPAController')->name('backend.fundraising.forms.view');
Route::get('fundraising/forms/{form}/performance-summary.csv', 'FundraisingFormController@exportPerformanceSummary');

// Legacy routes
Route::get('index.php', 'SessionController@index');
Route::get('product/detail', 'ProductController@view');
Route::get('product/orders/detail', 'OrderController@view');

// Legacy members routes
Route::permanentRedirectWithQueryString('members', '/jpanel/supporters');
Route::permanentRedirectWithQueryString('members.ajax', '/jpanel/supporter.ajax');
Route::permanentRedirectWithQueryString('members/destroy', '/jpanel/supporters/destroy');
Route::permanentRedirectWithQueryString('members/export/emails', '/jpanel/supporters/export/emails');
Route::permanentRedirectWithQueryString('members/export/all', '/jpanel/supporters/export/all');
Route::permanentRedirectWithQueryString('members/save', '/jpanel/supporters/save');
Route::permanentRedirectWithQueryString('members/add', '/jpanel/supporters/add');

Route::get('members/edit', function (Illuminate\Http\Request $request) {
    return redirect(route('backend.member.edit', $request->i))->withQueryString($request->except('i'));
});

Route::permanentRedirectWithQueryString('members/{id}/merge', '/jpanel/supporters/{id}/merge');
Route::permanentRedirectWithQueryString('members.json', '/jpanel/supporters.json');
Route::permanentRedirectWithQueryString('members/{id}/login', '/jpanel/supporters/{id}/login');
Route::permanentRedirectWithQueryString('members/{id}/restore', '/jpanel/supporters/{id}/restore');
Route::permanentRedirectWithQueryString('members/{id}/payment_methods/import_from_vault', '/jpanel/supporters/{id}/payment_methods/import_from_vault');

Route::group(['prefix' => 'settings'], function () {
    Route::permanentRedirect('accounts', '/jpanel/supporters');
    Route::permanentRedirect('accounts/save', '/jpanel/supporters/save');
});

// legacy order routes
Route::permanentRedirectWithQueryString('orders', '/jpanel/contributions');
Route::permanentRedirectWithQueryString('orders.ajax', '/jpanel/contributions.ajax');
Route::permanentRedirectWithQueryString('orders.csv', '/jpanel/contributions.csv');
Route::permanentRedirectWithQueryString('orders_with_items.csv', '/jpanel/contributions_with_items.csv');
Route::permanentRedirectWithQueryString('orders/update', '/jpanel/contributions/update');
Route::permanentRedirectWithQueryString('orders/add', '/jpanel/contributions/add');
Route::permanentRedirectWithQueryString('orders/edit', '/jpanel/contributions/edit');

Route::get('orders/edit', function (Illuminate\Http\Request $request) {
    return redirect(route('backend.orders.edit', $request->i))->withQueryString($request->except('i'));
});

Route::permanentRedirectWithQueryString('orders/set_vault', '/jpanel/contributions/set_vault');
Route::permanentRedirectWithQueryString('orders/notify_site_owner', '/jpanel/contributions/notify_site_owner');
Route::permanentRedirectWithQueryString('orders/push_to_dpo', '/jpanel/contributions/push_to_dpo');

Route::get('orders/reprocess_downloads', function (Illuminate\Http\Request $request) {
    return redirect(route('backend.orders.reprocess_downloads', $request->i))->withQueryString($request->except('i'));
});

Route::permanentRedirectWithQueryString('orders/reprocess_product_specific_emails', '/jpanel/contributions/reprocess_product_specific_emails');
Route::permanentRedirectWithQueryString('orders/abandoned_carts', '/jpanel/contributions/abandoned_carts');
Route::permanentRedirectWithQueryString('orders/abandoned_carts.ajax', '/jpanel/contributions/abandoned_carts.ajax');
Route::permanentRedirectWithQueryString('orders/abandoned_carts.csv', '/jpanel/contributions/abandoned_carts.csv');
Route::permanentRedirectWithQueryString('orders/batch', '/jpanel/contributions/batch');
Route::permanentRedirectWithQueryString('orders/packing_slip', '/jpanel/contributions/packing_slip');
Route::permanentRedirectWithQueryString('orders/custom_fields.csv', '/jpanel/contributions/custom_fields.csv');
Route::permanentRedirectWithQueryString('orders/checkin', '/jpanel/contributions/checkin');
Route::permanentRedirectWithQueryString('order/{order_id}/generate_tax_receipt', '/jpanel/contributions/{order_id}/generate_tax_receipt');
Route::permanentRedirectWithQueryString('order/{id}/refund', '/jpanel/contributions/{id}/refund');
Route::permanentRedirectWithQueryString('orders/number/{invoice_number}', '/jpanel/contributions/orders/number/{invoice_number}');
Route::permanentRedirectWithQueryString('orders/{id}/linkMember', '/jpanel/contributions/orders/{id}/linkMember');
Route::permanentRedirectWithQueryString('orders/{id}/createMember', '/jpanel/contributions/orders/{id}/createMember');
Route::permanentRedirectWithQueryString('orders/{id}/unlinkMember', '/jpanel/contributions/orders/{id}/unlinkMember');
Route::permanentRedirectWithQueryString('orders/{id}/editDPData', '/jpanel/contributions/orders/{id}/editDPData');
Route::permanentRedirectWithQueryString('orders/{id}/editItem', '/jpanel/contributions/orders/{id}/editItem');
Route::permanentRedirectWithQueryString('orders/{id}/editItemFields', '/jpanel/contributions/orders/{id}/editItemFields');
Route::permanentRedirectWithQueryString('orders/{id}/editGiftAidEligibility', '/jpanel/contributions/orders/{id}/editGiftAidEligibility');
Route::permanentRedirectWithQueryString('orders/{id}/getItemFields', '/jpanel/contributions/orders/{id}/getItemFields');
Route::permanentRedirectWithQueryString('orders/{id}/destroy', '/jpanel/contributions/orders/{id}/destroy');
Route::permanentRedirectWithQueryString('orders/{id}/restore', '/jpanel/contributions/orders/{id}/restore');
Route::permanentRedirectWithQueryString('orders/{id}/complete', '/jpanel/contributions/orders/{id}/complete');
Route::permanentRedirectWithQueryString('orders/{id}/incomplete', '/jpanel/contributions/orders/{id}/incomplete');

// legacy account types routes
Route::permanentRedirectWithQueryString('account_types/{id}/edit', '/jpanel/supporter_types/{id}/edit');
Route::permanentRedirectWithQueryString('account_types/{id}/update', '/jpanel/supporter_types/{id}/update');
Route::permanentRedirectWithQueryString('account_types/{id}/destroy', '/jpanel/supporter_types/{id}/destroy');
Route::permanentRedirectWithQueryString('account_types/add', '/jpanel/supporter_types/add');
Route::permanentRedirectWithQueryString('account_types/new', '/jpanel/supporter_types/new');

// legacy product order routes
Route::permanentRedirectWithQueryString('products/{id}/orders', '/jpanel/products/{id}/contributions');
Route::permanentRedirectWithQueryString('products/{id}/orders.ajax', '/jpanel/products/{id}/contributions.ajax');
Route::permanentRedirectWithQueryString('products/{id}/orders.csv', '/jpanel/products/{id}/contributions.csv');
Route::permanentRedirectWithQueryString('products/{id}/orders-with-items.csv', '/jpanel/products/{id}/contributions-with-items.csv');

// legacy fundraising page routes
Route::permanentRedirectWithQueryString('fundraising-pages/{id}/orders.json', 'fundraising-pages/{id}/contributions.json');
Route::permanentRedirectWithQueryString('fundraising-pages/{id}/orders.csv', 'fundraising-pages/{id}/contributions.csv');

// legacy transaction fees
Route::permanentRedirectWithQueryString('reports/transaction-fees', 'platform-fees');
Route::permanentRedirectWithQueryString('reports/transaction-fees.csv', 'platform-fees.csv');
Route::permanentRedirectWithQueryString('reports/transaction-fees.json', 'platform-fees.json');

// Catch all
Route::get('/', 'SPAController')->name('backend.session.index')->withoutMiddleware(['track.visit']);
Route::get('{path}', 'SPAController')->where('path', '.+')->withoutMiddleware(['track.visit']);
