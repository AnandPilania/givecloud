<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToFeatureCdnTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('autologin_tokens', function (Blueprint $table) {
            $table->foreign('account_id', 'autologin_tokens_ibfk_1')->references('id')->on('member')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });

        Schema::table('emails', function (Blueprint $table) {
            $table->foreign('created_by', 'emails_ibfk_1')->references('id')->on('user')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('updated_by', 'emails_ibfk_2')->references('id')->on('user')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });

        Schema::table('fundraising_page_members', function (Blueprint $table) {
            $table->foreign('fundraising_page_id', 'fundraising_page_members_ibfk_1')->references('id')->on('fundraising_pages')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('member_id', 'fundraising_page_members_ibfk_2')->references('id')->on('member')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });

        Schema::table('fundraising_page_reports', function (Blueprint $table) {
            $table->foreign('fundraising_page_id', 'fundraising_page_reports_ibfk_1')->references('id')->on('fundraising_pages')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('member_id', 'fundraising_page_reports_ibfk_2')->references('id')->on('member')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });

        Schema::table('fundraising_pages', function (Blueprint $table) {
            $table->foreign('product_id', 'fundraising_pages_ibfk_1')->references('id')->on('product')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('member_organizer_id', 'fundraising_pages_ibfk_3')->references('id')->on('member')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('photo_id', 'fundraising_pages_ibfk_4')->references('id')->on('media')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('team_photo_id', 'fundraising_pages_ibfk_5')->references('id')->on('media')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('created_by', 'fundraising_pages_ibfk_6')->references('id')->on('user')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('updated_by', 'fundraising_pages_ibfk_7')->references('id')->on('user')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('deleted_by', 'fundraising_pages_ibfk_8')->references('id')->on('user')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });

        Schema::table('hook_deliveries', function (Blueprint $table) {
            $table->foreign('hook_id', 'hook_deliveries_ibfk_1')->references('id')->on('hooks')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });

        Schema::table('images', function (Blueprint $table) {
            $table->foreign('media_id', 'images_ibfk_1')->references('id')->on('media')->onUpdate('RESTRICT')->onDelete('SET NULL');
        });

        Schema::table('kiosk_sessions', function (Blueprint $table) {
            $table->foreign('kiosk_id', 'kiosk_sessions_ibfk_1')->references('id')->on('kiosks')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('user_id', 'kiosk_sessions_ibfk_2')->references('id')->on('user')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });

        Schema::table('kiosks', function (Blueprint $table) {
            $table->foreign('product_id', 'kiosks_ibfk_1')->references('id')->on('product')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });

        Schema::table('media', function (Blueprint $table) {
            $table->foreign('parent_id', 'media_ibfk_1')->references('id')->on('media')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });

        Schema::table('member_login', function (Blueprint $table) {
            $table->foreign('member_id', 'member_login_ibfk_2')->references('id')->on('member')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });

        Schema::table('member', function (Blueprint $table) {
            $table->foreign('updated_by', 'member_ibfk_2')->references('id')->on('user')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('account_type_id', 'member_ibfk_4')->references('id')->on('account_types')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });

        Schema::table('membership_access', function (Blueprint $table) {
            $table->foreign('membership_id', 'membership_access_ibfk_1')->references('id')->on('membership')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });

        Schema::table('membership', function (Blueprint $table) {
            $table->foreign('created_by', 'membership_ibfk_1')->references('id')->on('user')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('updated_by', 'membership_ibfk_2')->references('id')->on('user')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });

        Schema::table('nodecontent', function (Blueprint $table) {
            $table->foreign('createdbyuserid', 'nodecontent_ibfk_1')->references('id')->on('user')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('modifiedbyuserid', 'nodecontent_ibfk_2')->references('id')->on('user')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('nodeid', 'nodecontent_ibfk_3')->references('id')->on('node')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('layoutid', 'nodecontent_ibfk_4')->references('id')->on('layout')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('featured_image_id', 'nodecontent_ibfk_5')->references('id')->on('media')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('alt_image_id', 'nodecontent_ibfk_6')->references('id')->on('media')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });

        Schema::table('payment_methods', function (Blueprint $table) {
            $table->foreign('member_id', 'payment_methods_ibfk_2')->references('id')->on('member')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });

        Schema::table('payment_option_group', function (Blueprint $table) {
            $table->foreign('created_by', 'payment_option_group_ibfk_1')->references('id')->on('user')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('updated_by', 'payment_option_group_ibfk_2')->references('id')->on('user')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });

        Schema::table('payment_option', function (Blueprint $table) {
            $table->foreign('created_by', 'payment_option_ibfk_1')->references('id')->on('user')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('updated_by', 'payment_option_ibfk_2')->references('id')->on('user')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('group_id', 'payment_option_ibfk_3')->references('id')->on('payment_option_group')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });

        Schema::table('payments_pivot', function (Blueprint $table) {
            $table->foreign('payment_id', 'payments_pivot_ibfk_1')->references('id')->on('payments')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('order_id', 'payments_pivot_ibfk_2')->references('id')->on('productorder')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('recurring_payment_profile_id', 'payments_pivot_ibfk_4')->references('id')->on('recurring_payment_profiles')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('transaction_id', 'payments_pivot_ibfk_5')->references('id')->on('transactions')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->foreign('source_account_id', 'payments_ibfk_2')->references('id')->on('member')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('source_payment_method_id', 'payments_ibfk_3')->references('id')->on('payment_methods')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });

        Schema::table('popup_logs', function (Blueprint $table) {
            $table->foreign('popup_id', 'popup_logs_ibfk_1')->references('id')->on('popups')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });

        Schema::table('popups', function (Blueprint $table) {
            $table->foreign('created_by', 'popups_ibfk_1')->references('id')->on('user')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('updated_by', 'popups_ibfk_2')->references('id')->on('user')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('node_id', 'popups_ibfk_3')->references('id')->on('node')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });

        Schema::table('post', function (Blueprint $table) {
            $table->foreign('modifiedbyuserid', 'post_ibfk_1')->references('id')->on('user')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('type', 'post_ibfk_2')->references('id')->on('posttype')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('media_id', 'post_ibfk_3')->references('id')->on('media')->onUpdate('RESTRICT')->onDelete('SET NULL');
            $table->foreign('featured_image_id', 'post_ibfk_4')->references('id')->on('media')->onUpdate('RESTRICT')->onDelete('SET NULL');
            $table->foreign('alt_image_id', 'post_ibfk_5')->references('id')->on('media')->onUpdate('RESTRICT')->onDelete('SET NULL');
        });

        Schema::table('posttype', function (Blueprint $table) {
            $table->foreign('media_id', 'posttype_ibfk_1')->references('id')->on('media')->onUpdate('RESTRICT')->onDelete('SET NULL');
        });

        Schema::table('product', function (Blueprint $table) {
            $table->foreign('createdbyuserid', 'product_ibfk_1')->references('id')->on('user')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('modifiedbyuserid', 'product_ibfk_2')->references('id')->on('user')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('media_id', 'product_ibfk_3')->references('id')->on('media')->onUpdate('RESTRICT')->onDelete('SET NULL');
        });

        Schema::table('productaudioclip', function (Blueprint $table) {
            $table->foreign('productid', 'productaudioclip_ibfk_1')->references('id')->on('product')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });

        Schema::table('productcategory', function (Blueprint $table) {
            $table->foreign('parent_id', 'productcategory_ibfk_1')->references('id')->on('productcategory')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('media_id', 'productcategory_ibfk_2')->references('id')->on('media')->onUpdate('RESTRICT')->onDelete('SET NULL');
        });

        Schema::table('productcategorylink', function (Blueprint $table) {
            $table->foreign('productid', 'productcategorylink_ibfk_3')->references('id')->on('product')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('categoryid', 'productcategorylink_ibfk_4')->references('id')->on('productcategory')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });

        Schema::table('productfields', function (Blueprint $table) {
            $table->foreign('productid', 'productfields_ibfk_1')->references('id')->on('product')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('deleted_by', 'productfields_ibfk_2')->references('id')->on('user')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });

        Schema::table('productinventory', function (Blueprint $table) {
            $table->foreign('quantitymodifiedbyuserid', 'productinventory_ibfk_2')->references('id')->on('user')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('membership_id', 'productinventory_ibfk_3')->references('id')->on('membership')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('productid', 'productinventory_ibfk_4')->references('id')->on('product')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });

        Schema::table('productinventoryfiles', function (Blueprint $table) {
            $table->foreign('fileid', 'productinventoryfiles_ibfk_1')->references('id')->on('files')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('inventoryid', 'productinventoryfiles_ibfk_3')->references('id')->on('productinventory')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });

        Schema::table('productorder', function (Blueprint $table) {
            $table->foreign('shipping_method_id', 'productorder_ibfk_1')->references('id')->on('shipping_method')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('member_id', 'productorder_ibfk_2')->references('id')->on('member')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('account_type_id', 'productorder_ibfk_3')->references('id')->on('account_types')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });

        Schema::table('productorderitem', function (Blueprint $table) {
            $table->foreign('fundraising_member_id', 'productorderitem_ibfk_10')->references('id')->on('member')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('DELETE_productid', 'productorderitem_ibfk_2')->references('id')->on('product')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('productinventoryid', 'productorderitem_ibfk_3')->references('id')->on('productinventory')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('productorderid', 'productorderitem_ibfk_5')->references('id')->on('productorder')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('locked_to_item_id', 'productorderitem_ibfk_6')->references('id')->on('productorderitem')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('tribute_type_id', 'productorderitem_ibfk_7')->references('id')->on('tribute_types')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('original_variant_id', 'productorderitem_ibfk_8')->references('id')->on('productinventory')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('fundraising_page_id', 'productorderitem_ibfk_9')->references('id')->on('fundraising_pages')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });

        Schema::table('productorderitemfield', function (Blueprint $table) {
            $table->foreign('orderitemid', 'productorderitemfield_ibfk_2')->references('id')->on('productorderitem')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('fieldid', 'productorderitemfield_ibfk_3')->references('id')->on('productfields')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });

        Schema::table('productorderitemfiles', function (Blueprint $table) {
            $table->foreign('fileid', 'productorderitemfiles_ibfk_1')->references('id')->on('files')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('orderitemid', 'productorderitemfiles_ibfk_4')->references('id')->on('productorderitem')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });

        Schema::table('productorderitemtax', function (Blueprint $table) {
            $table->foreign('taxid', 'productorderitemtax_ibfk_2')->references('id')->on('producttax')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('orderitemid', 'productorderitemtax_ibfk_3')->references('id')->on('productorderitem')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });

        Schema::table('productpromocode', function (Blueprint $table) {
            $table->foreign('createdbyuserid', 'productpromocode_ibfk_1')->references('id')->on('user')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('modifiedbyuserid', 'productpromocode_ibfk_2')->references('id')->on('user')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });

        Schema::table('productpromocodecategory', function (Blueprint $table) {
            $table->foreign('categoryid', 'productpromocodecategory_ibfk_3')->references('id')->on('productcategory')->onUpdate('RESTRICT')->onDelete('CASCADE');
            $table->foreign('promocodeid', 'productpromocodecategory_ibfk_4')->references('id')->on('productpromocode')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });

        Schema::table('productpromocodeproduct', function (Blueprint $table) {
            $table->foreign('productid', 'productpromocodeproduct_ibfk_2')->references('id')->on('product')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('promocodeid', 'productpromocodeproduct_ibfk_3')->references('id')->on('productpromocode')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });

        Schema::table('producttax', function (Blueprint $table) {
            $table->foreign('createdbyuserid', 'producttax_ibfk_1')->references('id')->on('user')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('modifiedbyuserid', 'producttax_ibfk_2')->references('id')->on('user')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('deleted_by', 'producttax_ibfk_3')->references('id')->on('user')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });

        Schema::table('producttaxproduct', function (Blueprint $table) {
            $table->foreign('productid', 'producttaxproduct_ibfk_1')->references('id')->on('product')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('taxid', 'producttaxproduct_ibfk_2')->references('id')->on('producttax')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });

        Schema::table('producttaxregion', function (Blueprint $table) {
            $table->foreign('taxid', 'producttaxregion_ibfk_1')->references('id')->on('producttax')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('regionid', 'producttaxregion_ibfk_2')->references('id')->on('region')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });

        Schema::table('recurring_payment_profiles', function (Blueprint $table) {
            $table->foreign('payment_method_id', 'recurring_payment_profiles_ibfk_3')->references('id')->on('payment_methods')->onUpdate('RESTRICT')->onDelete('SET NULL');
            $table->foreign('member_id', 'recurring_payment_profiles_ibfk_4')->references('id')->on('member')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('productorder_id', 'recurring_payment_profiles_ibfk_5')->references('id')->on('productorder')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('productorderitem_id', 'recurring_payment_profiles_ibfk_6')->references('id')->on('productorderitem')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('productinventory_id', 'recurring_payment_profiles_ibfk_7')->references('id')->on('productinventory')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('product_id', 'recurring_payment_profiles_ibfk_8')->references('id')->on('product')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });

        Schema::table('refunds', function (Blueprint $table) {
            $table->foreign('payment_id', 'refunds_ibfk_1')->references('id')->on('payments')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('refunded_by_id', 'refunds_ibfk_2')->references('id')->on('user')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });

        Schema::table('segment_items', function (Blueprint $table) {
            $table->foreign('segment_id', 'segment_items_ibfk_1')->references('id')->on('segments')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('created_by', 'segment_items_ibfk_2')->references('id')->on('user')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('updated_by', 'segment_items_ibfk_3')->references('id')->on('user')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });

        Schema::table('segments', function (Blueprint $table) {
            $table->foreign('created_by', 'segments_ibfk_1')->references('id')->on('user')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('updated_by', 'segments_ibfk_2')->references('id')->on('user')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });

        Schema::table('shipping_method', function (Blueprint $table) {
            $table->foreign('created_by', 'shipping_method_ibfk_1')->references('id')->on('user')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('updated_by', 'shipping_method_ibfk_2')->references('id')->on('user')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('deleted_by', 'shipping_method_ibfk_3')->references('id')->on('user')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });

        Schema::table('shipping_tier', function (Blueprint $table) {
            $table->foreign('created_by', 'shipping_tier_ibfk_1')->references('id')->on('user')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('updated_by', 'shipping_tier_ibfk_2')->references('id')->on('user')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });

        Schema::table('shipping_value', function (Blueprint $table) {
            $table->foreign('method_id', 'shipping_value_ibfk_1')->references('id')->on('shipping_method')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('tier_id', 'shipping_value_ibfk_2')->references('id')->on('shipping_tier')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });

        Schema::table('sponsorship_segments', function (Blueprint $table) {
            $table->foreign('sponsorship_id', 'sponsorship_segments_ibfk_1')->references('id')->on('sponsorship')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('segment_id', 'sponsorship_segments_ibfk_2')->references('id')->on('segments')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('segment_item_id', 'sponsorship_segments_ibfk_3')->references('id')->on('segment_items')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('created_by', 'sponsorship_segments_ibfk_4')->references('id')->on('user')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('updated_by', 'sponsorship_segments_ibfk_5')->references('id')->on('user')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });

        Schema::table('sponsorship', function (Blueprint $table) {
            $table->foreign('created_by', 'sponsorship_ibfk_1')->references('id')->on('user')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('updated_by', 'sponsorship_ibfk_2')->references('id')->on('user')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('media_id', 'sponsorship_ibfk_3')->references('id')->on('media')->onUpdate('RESTRICT')->onDelete('SET NULL');
        });

        Schema::table('taggables', function (Blueprint $table) {
            $table->foreign('tag_id', 'taggables_ibfk_1')->references('id')->on('tags')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });

        Schema::table('tax_receipts', function (Blueprint $table) {
            $table->foreign('order_id', 'tax_receipts_ibfk_1')->references('id')->on('productorder')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('transaction_id', 'tax_receipts_ibfk_2')->references('id')->on('transactions')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });

        Schema::table('ticket_check_in', function (Blueprint $table) {
            $table->foreign('order_id', 'ticket_check_in_ibfk_1')->references('id')->on('productorder')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('order_item_id', 'ticket_check_in_ibfk_2')->references('id')->on('productorderitem')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('check_in_by', 'ticket_check_in_ibfk_3')->references('id')->on('user')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->foreign('recurring_payment_profile_id', 'transactions_ibfk_2')->references('id')->on('recurring_payment_profiles')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('payment_method_id', 'transactions_ibfk_4')->references('id')->on('payment_methods')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });

        Schema::table('tributes', function (Blueprint $table) {
            $table->foreign('order_item_id', 'tributes_ibfk_1')->references('id')->on('productorderitem')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('tribute_type_id', 'tributes_ibfk_2')->references('id')->on('tribute_types')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });

        Schema::table('user_logins', function (Blueprint $table) {
            $table->foreign('user_id', 'user_logins_ibfk_1')->references('id')->on('user')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });

        Schema::table('user', function (Blueprint $table) {
            $table->foreign('deleted_by', 'user_ibfk_1')->references('id')->on('user')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });

        Schema::table('userpermission', function (Blueprint $table) {
            $table->foreign('userid', 'userpermission_ibfk_1')->references('id')->on('user')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });

        Schema::table('variant_variant', function (Blueprint $table) {
            $table->foreign('variant_id', 'variant_variant_ibfk_1')->references('id')->on('productinventory')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('linked_variant_id', 'variant_variant_ibfk_2')->references('id')->on('productinventory')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('autologin_tokens', function (Blueprint $table) {
            $table->dropForeign('autologin_tokens_ibfk_1');
        });

        Schema::table('emails', function (Blueprint $table) {
            $table->dropForeign('emails_ibfk_1');
            $table->dropForeign('emails_ibfk_2');
        });

        Schema::table('fundraising_page_members', function (Blueprint $table) {
            $table->dropForeign('fundraising_page_members_ibfk_1');
            $table->dropForeign('fundraising_page_members_ibfk_2');
        });

        Schema::table('fundraising_page_reports', function (Blueprint $table) {
            $table->dropForeign('fundraising_page_reports_ibfk_1');
            $table->dropForeign('fundraising_page_reports_ibfk_2');
        });

        Schema::table('fundraising_pages', function (Blueprint $table) {
            $table->dropForeign('fundraising_pages_ibfk_1');
            $table->dropForeign('fundraising_pages_ibfk_3');
            $table->dropForeign('fundraising_pages_ibfk_4');
            $table->dropForeign('fundraising_pages_ibfk_5');
            $table->dropForeign('fundraising_pages_ibfk_6');
            $table->dropForeign('fundraising_pages_ibfk_7');
            $table->dropForeign('fundraising_pages_ibfk_8');
        });

        Schema::table('hook_deliveries', function (Blueprint $table) {
            $table->dropForeign('hook_deliveries_ibfk_1');
        });

        Schema::table('images', function (Blueprint $table) {
            $table->dropForeign('images_ibfk_1');
        });

        Schema::table('kiosk_sessions', function (Blueprint $table) {
            $table->dropForeign('kiosk_sessions_ibfk_1');
            $table->dropForeign('kiosk_sessions_ibfk_2');
        });

        Schema::table('kiosks', function (Blueprint $table) {
            $table->dropForeign('kiosks_ibfk_1');
        });

        Schema::table('media', function (Blueprint $table) {
            $table->dropForeign('media_ibfk_1');
        });

        Schema::table('member_login', function (Blueprint $table) {
            $table->dropForeign('member_login_ibfk_2');
        });

        Schema::table('member', function (Blueprint $table) {
            $table->dropForeign('member_ibfk_2');
            $table->dropForeign('member_ibfk_4');
        });

        Schema::table('membership_access', function (Blueprint $table) {
            $table->dropForeign('membership_access_ibfk_1');
        });

        Schema::table('membership', function (Blueprint $table) {
            $table->dropForeign('membership_ibfk_1');
            $table->dropForeign('membership_ibfk_2');
        });

        Schema::table('nodecontent', function (Blueprint $table) {
            $table->dropForeign('nodecontent_ibfk_1');
            $table->dropForeign('nodecontent_ibfk_2');
            $table->dropForeign('nodecontent_ibfk_3');
            $table->dropForeign('nodecontent_ibfk_4');
            $table->dropForeign('nodecontent_ibfk_5');
            $table->dropForeign('nodecontent_ibfk_6');
        });

        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropForeign('payment_methods_ibfk_2');
        });

        Schema::table('payment_option_group', function (Blueprint $table) {
            $table->dropForeign('payment_option_group_ibfk_1');
            $table->dropForeign('payment_option_group_ibfk_2');
        });

        Schema::table('payment_option', function (Blueprint $table) {
            $table->dropForeign('payment_option_ibfk_1');
            $table->dropForeign('payment_option_ibfk_2');
            $table->dropForeign('payment_option_ibfk_3');
        });

        Schema::table('payments_pivot', function (Blueprint $table) {
            $table->dropForeign('payments_pivot_ibfk_1');
            $table->dropForeign('payments_pivot_ibfk_2');
            $table->dropForeign('payments_pivot_ibfk_4');
            $table->dropForeign('payments_pivot_ibfk_5');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropForeign('payments_ibfk_2');
            $table->dropForeign('payments_ibfk_3');
        });

        Schema::table('popup_logs', function (Blueprint $table) {
            $table->dropForeign('popup_logs_ibfk_1');
        });

        Schema::table('popups', function (Blueprint $table) {
            $table->dropForeign('popups_ibfk_1');
            $table->dropForeign('popups_ibfk_2');
            $table->dropForeign('popups_ibfk_3');
        });

        Schema::table('post', function (Blueprint $table) {
            $table->dropForeign('post_ibfk_1');
            $table->dropForeign('post_ibfk_2');
            $table->dropForeign('post_ibfk_3');
            $table->dropForeign('post_ibfk_4');
            $table->dropForeign('post_ibfk_5');
        });

        Schema::table('posttype', function (Blueprint $table) {
            $table->dropForeign('posttype_ibfk_1');
        });

        Schema::table('product', function (Blueprint $table) {
            $table->dropForeign('product_ibfk_1');
            $table->dropForeign('product_ibfk_2');
            $table->dropForeign('product_ibfk_3');
        });

        Schema::table('productaudioclip', function (Blueprint $table) {
            $table->dropForeign('productaudioclip_ibfk_1');
        });

        Schema::table('productcategory', function (Blueprint $table) {
            $table->dropForeign('productcategory_ibfk_1');
            $table->dropForeign('productcategory_ibfk_2');
        });

        Schema::table('productcategorylink', function (Blueprint $table) {
            $table->dropForeign('productcategorylink_ibfk_3');
            $table->dropForeign('productcategorylink_ibfk_4');
        });

        Schema::table('productfields', function (Blueprint $table) {
            $table->dropForeign('productfields_ibfk_1');
            $table->dropForeign('productfields_ibfk_2');
        });

        Schema::table('productinventory', function (Blueprint $table) {
            $table->dropForeign('productinventory_ibfk_2');
            $table->dropForeign('productinventory_ibfk_3');
            $table->dropForeign('productinventory_ibfk_4');
        });

        Schema::table('productinventoryfiles', function (Blueprint $table) {
            $table->dropForeign('productinventoryfiles_ibfk_1');
            $table->dropForeign('productinventoryfiles_ibfk_3');
        });

        Schema::table('productorder', function (Blueprint $table) {
            $table->dropForeign('productorder_ibfk_1');
            $table->dropForeign('productorder_ibfk_2');
            $table->dropForeign('productorder_ibfk_3');
        });

        Schema::table('productorderitem', function (Blueprint $table) {
            $table->dropForeign('productorderitem_ibfk_10');
            $table->dropForeign('productorderitem_ibfk_2');
            $table->dropForeign('productorderitem_ibfk_3');
            $table->dropForeign('productorderitem_ibfk_5');
            $table->dropForeign('productorderitem_ibfk_6');
            $table->dropForeign('productorderitem_ibfk_7');
            $table->dropForeign('productorderitem_ibfk_8');
            $table->dropForeign('productorderitem_ibfk_9');
        });

        Schema::table('productorderitemfield', function (Blueprint $table) {
            $table->dropForeign('productorderitemfield_ibfk_2');
            $table->dropForeign('productorderitemfield_ibfk_3');
        });

        Schema::table('productorderitemfiles', function (Blueprint $table) {
            $table->dropForeign('productorderitemfiles_ibfk_1');
            $table->dropForeign('productorderitemfiles_ibfk_4');
        });

        Schema::table('productorderitemtax', function (Blueprint $table) {
            $table->dropForeign('productorderitemtax_ibfk_2');
            $table->dropForeign('productorderitemtax_ibfk_3');
        });

        Schema::table('productpromocode', function (Blueprint $table) {
            $table->dropForeign('productpromocode_ibfk_1');
            $table->dropForeign('productpromocode_ibfk_2');
        });

        Schema::table('productpromocodecategory', function (Blueprint $table) {
            $table->dropForeign('productpromocodecategory_ibfk_3');
            $table->dropForeign('productpromocodecategory_ibfk_4');
        });

        Schema::table('productpromocodeproduct', function (Blueprint $table) {
            $table->dropForeign('productpromocodeproduct_ibfk_2');
            $table->dropForeign('productpromocodeproduct_ibfk_3');
        });

        Schema::table('producttax', function (Blueprint $table) {
            $table->dropForeign('producttax_ibfk_1');
            $table->dropForeign('producttax_ibfk_2');
            $table->dropForeign('producttax_ibfk_3');
        });

        Schema::table('producttaxproduct', function (Blueprint $table) {
            $table->dropForeign('producttaxproduct_ibfk_1');
            $table->dropForeign('producttaxproduct_ibfk_2');
        });

        Schema::table('producttaxregion', function (Blueprint $table) {
            $table->dropForeign('producttaxregion_ibfk_1');
            $table->dropForeign('producttaxregion_ibfk_2');
        });

        Schema::table('recurring_payment_profiles', function (Blueprint $table) {
            $table->dropForeign('recurring_payment_profiles_ibfk_3');
            $table->dropForeign('recurring_payment_profiles_ibfk_4');
            $table->dropForeign('recurring_payment_profiles_ibfk_5');
            $table->dropForeign('recurring_payment_profiles_ibfk_6');
            $table->dropForeign('recurring_payment_profiles_ibfk_7');
            $table->dropForeign('recurring_payment_profiles_ibfk_8');
        });

        Schema::table('refunds', function (Blueprint $table) {
            $table->dropForeign('refunds_ibfk_1');
            $table->dropForeign('refunds_ibfk_2');
        });

        Schema::table('segment_items', function (Blueprint $table) {
            $table->dropForeign('segment_items_ibfk_1');
            $table->dropForeign('segment_items_ibfk_2');
            $table->dropForeign('segment_items_ibfk_3');
        });

        Schema::table('segments', function (Blueprint $table) {
            $table->dropForeign('segments_ibfk_1');
            $table->dropForeign('segments_ibfk_2');
        });

        Schema::table('shipping_method', function (Blueprint $table) {
            $table->dropForeign('shipping_method_ibfk_1');
            $table->dropForeign('shipping_method_ibfk_2');
            $table->dropForeign('shipping_method_ibfk_3');
        });

        Schema::table('shipping_tier', function (Blueprint $table) {
            $table->dropForeign('shipping_tier_ibfk_1');
            $table->dropForeign('shipping_tier_ibfk_2');
        });

        Schema::table('shipping_value', function (Blueprint $table) {
            $table->dropForeign('shipping_value_ibfk_1');
            $table->dropForeign('shipping_value_ibfk_2');
        });

        Schema::table('sponsorship_segments', function (Blueprint $table) {
            $table->dropForeign('sponsorship_segments_ibfk_1');
            $table->dropForeign('sponsorship_segments_ibfk_2');
            $table->dropForeign('sponsorship_segments_ibfk_3');
            $table->dropForeign('sponsorship_segments_ibfk_4');
            $table->dropForeign('sponsorship_segments_ibfk_5');
        });

        Schema::table('sponsorship', function (Blueprint $table) {
            $table->dropForeign('sponsorship_ibfk_1');
            $table->dropForeign('sponsorship_ibfk_2');
            $table->dropForeign('sponsorship_ibfk_3');
        });

        Schema::table('taggables', function (Blueprint $table) {
            $table->dropForeign('taggables_ibfk_1');
        });

        Schema::table('tax_receipts', function (Blueprint $table) {
            $table->dropForeign('tax_receipts_ibfk_1');
            $table->dropForeign('tax_receipts_ibfk_2');
        });

        Schema::table('ticket_check_in', function (Blueprint $table) {
            $table->dropForeign('ticket_check_in_ibfk_1');
            $table->dropForeign('ticket_check_in_ibfk_2');
            $table->dropForeign('ticket_check_in_ibfk_3');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign('transactions_ibfk_2');
            $table->dropForeign('transactions_ibfk_4');
        });

        Schema::table('tributes', function (Blueprint $table) {
            $table->dropForeign('tributes_ibfk_1');
            $table->dropForeign('tributes_ibfk_2');
        });

        Schema::table('user_logins', function (Blueprint $table) {
            $table->dropForeign('user_logins_ibfk_1');
        });

        Schema::table('user', function (Blueprint $table) {
            $table->dropForeign('user_ibfk_1');
        });

        Schema::table('userpermission', function (Blueprint $table) {
            $table->dropForeign('userpermission_ibfk_1');
        });

        Schema::table('variant_variant', function (Blueprint $table) {
            $table->dropForeign('variant_variant_ibfk_1');
            $table->dropForeign('variant_variant_ibfk_2');
        });
    }
}
