<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AlterFeatureCdnTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->custom('theme_id', 'int(11)')->after('id')->index('theme_id');
            $table->custom('parent_id', 'int(11)')->nullable()->after('theme_id')->index('parent_id');
            $table->boolean('locked')->default(0)->after('filename');
            $table->custom('size', 'int(11)')->after('type');
            $table->string('public_url', 255)->nullable()->after('size');
            $table->renameColumn('filename', 'key');
            $table->renameColumn('type', 'content_type');
            $table->renameColumn('data', 'value');
            $table->foreign('parent_id', 'assets_ibfk_1')->references('id')->on('assets')->onDelete('CASCADE')->onUpdate('RESTRICT');
            $table->foreign('theme_id', 'assets_ibfk_2')->references('id')->on('themes')->onDelete('CASCADE')->onUpdate('RESTRICT');
        });

        Schema::table('assets', function (Blueprint $table) {
            $table->string('content_type', 64)->nullable(false)->change();
            $table->string('key', 255)->nullable(false)->change();
        });

        Schema::table('member', function (Blueprint $table) {
            $table->string('authorizenet_customer_id', 40)->nullable()->after('donor_id');
            $table->string('bill_phone_e164', 45)->nullable()->after('bill_phone')->index('bill_phone_e164');
            $table->boolean('sms_verified')->default(0)->after('bill_phone_e164');
            $table->string('paysafe_profile_id', 40)->nullable()->after('authorizenet_customer_id');
            $table->string('stripe_customer_id', 64)->nullable()->after('paysafe_profile_id');
            $table->string('vanco_customer_ref', 40)->nullable()->after('stripe_customer_id');
        });

        Schema::table('node', function (Blueprint $table) {
            $table->string('template_suffix', 64)->nullable()->after('pagetitle');
        });

        Schema::table('payment_methods', function (Blueprint $table) {
            $table->custom('payment_provider_id', 'int(11)')->unsigned()->after('member_id')->index('payment_provider_id');
            $table->foreign('payment_provider_id', 'payment_methods_ibfk_3')->references('id')->on('payment_providers')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });

        Schema::table('pledgables', function (Blueprint $table) {
            $table->dropPrimary();
        });

        Schema::table('pledgables', function (Blueprint $table) {
            $table->dropColumn('pledge_id');
            $table->custom('pledge_campaign_id', 'int(11)')->unsigned()->first();
            $table->primary(['pledge_campaign_id', 'pledgable_id', 'pledgable_type'], 'composite_primary_key');
        });

        Schema::table('pledges', function (Blueprint $table) {
            $table->custom('pledge_campaign_id', 'int(11)')->unsigned()->after('id');
        });

        Schema::table('posttype', function (Blueprint $table) {
            $table->string('template_suffix', 64)->nullable()->after('sysname');
            $table->string('default_template_suffix', 64)->nullable()->after('template_suffix');
        });

        Schema::table('product', function (Blueprint $table) {
            $table->string('template_name', 64)->nullable()->after('thank_you_email_template');
            $table->string('template_suffix', 64)->nullable()->after('permalink');
            $table->renameColumn('min_price', '_drop_min_price');
            $table->renameColumn('recurring_type', '_drop_recurring_type');
            $table->renameColumn('recurring_initial_charge', '_drop_recurring_initial_charge');
            $table->renameColumn('recurringinterval', '_drop_recurringinterval');
            $table->renameColumn('isrecurring', '_drop_isrecurring');
            $table->renameColumn('isdonation', '_drop_isdonation');
            $table->dropColumn('sizes');
            $table->dropColumn('category');
            $table->dropColumn('ismale');
            $table->dropColumn('isfemale');
            $table->dropColumn('saleprice');
            $table->dropColumn('price');
        });

        Schema::table('product', function (Blueprint $table) {
            $table->double('_drop_min_price')->nullable()->change();
        });

        Schema::table('productcategory', function (Blueprint $table) {
            $table->string('template_suffix', 64)->nullable()->after('url_name');
        });

        Schema::table('productinventory', function (Blueprint $table) {
            $table->custom('last_physical_count_id', 'int(11)')->nullable()->after('quantitymodifiedbyuserid')->index('last_physical_count_id');
            $table->custom('shipping_expectation_threshold', 'int(11)')->nullable()->after('quantityrestock');
            $table->string('sku', 25)->nullable()->after('cost');
            $table->string('shipping_expectation_over', 255)->nullable()->after('shipping_expectation_threshold');
            $table->string('shipping_expectation_under', 255)->nullable()->after('shipping_expectation_over');
            $table->string('barcode', 25)->nullable()->after('sku');
            $table->decimal('fair_market_value', 19, 4)->nullable()->after('barcode');
            $table->string('billing_schedule_type', 12)->nullable()->after('fair_market_value');
            $table->string('billing_period', 12)->default('onetime')->after('billing_schedule_type');
            $table->date('billing_starts_on')->nullable()->after('billing_period');
            $table->date('billing_ends_on')->nullable()->after('billing_starts_on');
            $table->custom('total_billing_cycles', 'smallint(5)')->unsigned()->nullable()->after('billing_ends_on');
            $table->string('price_presets', 500)->nullable()->comment('THIS SHOULD BE JSON')->after('total_billing_cycles');
            $table->custom('price_minimum', 'float')->nullable()->after('price_presets');
            $table->boolean('is_donation')->default(0)->after('price_minimum');
            $table->dateTime('created_at')->nullable()->after('is_donation');
            $table->dateTime('updated_at')->nullable()->after('created_at');
            $table->foreign('last_physical_count_id', 'productinventory_ibfk_5')->references('id')->on('stock_adjustments')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });

        Schema::table('productorder', function (Blueprint $table) {
            $table->custom('payment_method_id', 'int(11)')->nullable()->after('member_id')->index('payment_method_id');
            $table->custom('payment_provider_id', 'int(11)')->unsigned()->nullable()->after('billing_card_expiry_year')->index('payment_provider_id');
            $table->boolean('ship_to_billing')->default(0)->after('vault_id');
            $table->foreign('payment_method_id', 'productorder_ibfk_4')->references('id')->on('payment_methods')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('payment_provider_id', 'productorder_ibfk_5')->references('id')->on('payment_providers')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });

        Schema::table('productorderitem', function (Blueprint $table) {
            $table->custom('recurring_cycles', 'smallint(5)')->unsigned()->nullable()->after('recurring_with_initial_charge');
            $table->date('recurring_starts_on')->nullable()->after('recurring_cycles');
            $table->date('recurring_ends_on')->nullable()->after('recurring_starts_on');
            $table->dropForeign('productorderitem_ibfk_8');
        });

        Schema::table('productorderitem', function (Blueprint $table) {
            $table->foreign('original_variant_id', 'productorderitem_ibfk_8')->references('id')->on('productinventory')->onDelete('NO ACTION')->onUpdate('RESTRICT');
        });

        Schema::table('recurring_payment_profiles', function (Blueprint $table) {
            $table->string('cancel_reason', 64)->nullable()->after('sponsorship_id');
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->index(['theme_id'], 'theme_id');
            $table->foreign('theme_id', 'settings_ibfk_1')->references('id')->on('themes')->onUpdate('RESTRICT')->onDelete('CASCADE');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->string('transaction_id', 40)->nullable()->comment('Unique transaction ID of the payment.')->change();
            $table->string('parent_transaction_id', 40)->nullable()->comment('Parent or related transaction identification number.')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn([
                'theme_id',
                'parent_id',
                'locked',
                'size',
                'public_url',
            ]);
            $table->renameColumn('key', 'filename');
            $table->renameColumn('content_type', 'type');
            $table->renameColumn('value', 'data');
            $table->dropForeign('assets_ibfk_1');
            $table->dropForeign('assets_ibfk_2');
        });

        Schema::table('assets', function (Blueprint $table) {
            $table->string('type', 45)->nullable()->change();
            $table->string('filename', 255)->nullable()->change();
        });

        Schema::table('member', function (Blueprint $table) {
            $table->dropColumn([
                'authorizenet_customer_id',
                'bill_phone_e164',
                'sms_verified',
                'paysafe_profile_id',
                'stripe_customer_id',
                'vanco_customer_ref',
            ]);
        });

        Schema::table('node', function (Blueprint $table) {
            $table->dropColumn('template_suffix');
        });

        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropColumn('payment_provider_id');
            $table->dropForeign('payment_methods_ibfk_3');
        });

        Schema::table('pledgables', function (Blueprint $table) {
            $table->dropPrimary();
        });

        Schema::table('pledgables', function (Blueprint $table) {
            $table->dropColumn('pledge_campaign_id');
            $table->custom('pledge_id', 'int(11)')->unsigned();
            $table->primary(['pledge_id', 'pledgable_id', 'pledgable_type'], 'composite_primary_key');
        });

        Schema::table('pledges', function (Blueprint $table) {
            $table->dropColumn('pledge_campaign_id');
        });

        Schema::table('posttype', function (Blueprint $table) {
            $table->dropColumn(['template_suffix', 'default_template_suffix']);
        });

        Schema::table('product', function (Blueprint $table) {
            $table->dropColumn(['template_name', 'template_suffix']);
            $table->renameColumn('_drop_min_price', 'min_price');
            $table->renameColumn('_drop_recurring_type', 'recurring_type');
            $table->renameColumn('_drop_recurring_initial_charge', 'recurring_initial_charge');
            $table->renameColumn('_drop_recurringinterval', 'recurringinterval');
            $table->renameColumn('_drop_isrecurring', 'isrecurring');
            $table->renameColumn('_drop_isdonation', 'isdonation');
            $table->string('sizes', 45)->nullable()->after('code');
            $table->integer('category')->nullable()->after('show_in_pos');
            $table->boolean('ismale')->unsigned()->nullable()->after('category');
            $table->boolean('isfemale')->unsigned()->nullable()->after('ismale');
            $table->decimal('saleprice', 19, 4)->nullable()->after('media_id');
            $table->decimal('price', 19, 4)->nullable()->after('saleprice');
        });

        Schema::table('product', function (Blueprint $table) {
            $table->decimal('min_price', 19, 4)->nullable()->change();
        });

        Schema::table('productcategory', function (Blueprint $table) {
            $table->dropColumn('template_suffix');
        });

        Schema::table('productinventory', function (Blueprint $table) {
            $table->dropColumn([
                'last_physical_count_id',
                'shipping_expectation_threshold',
                'sku',
                'shipping_expectation_over',
                'shipping_expectation_under',
                'barcode',
                'fair_market_value',
                'billing_schedule_type',
                'billing_period',
                'billing_starts_on',
                'billing_ends_on',
                'total_billing_cycles',
                'price_presets',
                'price_minimum',
                'is_donation',
                'created_at',
                'updated_at',
            ]);
            $table->dropForeign('productinventory_ibfk_5');
        });

        Schema::table('productorder', function (Blueprint $table) {
            $table->dropColumn([
                'payment_method_id',
                'payment_provider_id',
                'ship_to_billing',
            ]);
            $table->dropForeign('productorder_ibfk_4');
            $table->dropForeign('productorder_ibfk_5');
        });

        Schema::table('productorderitem', function (Blueprint $table) {
            $table->dropColumn([
                'recurring_cycles',
                'recurring_starts_on',
                'recurring_ends_on',
            ]);
            $table->dropForeign('productorderitem_ibfk_8');
        });

        Schema::table('productorderitem', function (Blueprint $table) {
            $table->foreign('original_variant_id', 'productorderitem_ibfk_8')->references('id')->on('productinventory')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });

        Schema::table('recurring_payment_profiles', function (Blueprint $table) {
            $table->dropColumn('cancel_reason');
        });

        Schema::table('settings', function (Blueprint $table) {
            $table->dropIndex('theme_id');
            $table->dropForeign('settings_ibfk_1');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->string('transaction_id', 18)->nullable()->comment('Unique transaction ID of the payment.')->change();
            $table->string('parent_transaction_id', 18)->nullable()->comment('Parent or related transaction identification number.')->change();
        });
    }
}
