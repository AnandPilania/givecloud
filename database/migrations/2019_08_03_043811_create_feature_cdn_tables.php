<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFeatureCdnTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('account_types', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('name', 32);
            $table->boolean('is_organization')->default(0);
            $table->boolean('is_default')->default(0);
            $table->boolean('sequence')->default(0);
            $table->string('dp_code', 100)->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->integer('created_by');
            $table->integer('updated_by');
            $table->dateTime('deleted_at')->nullable();
            $table->boolean('on_web')->default(1);
        });

        Schema::create('aliases', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('source');
            $table->string('alias');
            $table->integer('status_code')->default(301);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        Schema::create('assets', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('type', 45)->nullable();
            $table->string('filename')->nullable();
            $table->longText('data')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
        });

        Schema::create('audits', function (Blueprint $table) {
            $table->increments('id');
            $table->string('user_type')->nullable();
            $table->bigInteger('user_id')->unsigned()->nullable();
            $table->string('event');
            $table->string('auditable_type');
            $table->bigInteger('auditable_id')->unsigned();
            $table->text('old_values', 65535)->nullable();
            $table->text('new_values', 65535)->nullable();
            $table->text('url', 65535)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->string('tags')->nullable();
            $table->timestamps();
            $table->index([DB::raw('`auditable_type`(191)'), 'auditable_id'], 'audits_auditable_type_auditable_id_index');
            $table->index(['user_id', DB::raw('`user_type`(191)')], 'audits_user_id_user_type_index');
        });

        Schema::create('autologin_tokens', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('account_id')->index('account_id');
            $table->char('hashed_token', 40)->unique('hashed_token');
            $table->text('path', 65535)->nullable();
            $table->integer('hits')->default(0);
            $table->dateTime('expires')->nullable();
            $table->boolean('kamikaze')->default(0);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        Schema::create('configs', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('config_key', 191)->collation('utf8mb4_bin')->index('config_key');
            $table->longText('config_value')->nullable()->collation('utf8mb4_bin');
            $table->dateTime('created_at')->nullable();
            $table->integer('created_by');
        });

        Schema::create('emails', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('type', 45)->nullable();
            $table->string('name', 200)->nullable();
            $table->string('subject', 500)->nullable();
            $table->string('to', 500)->nullable();
            $table->string('cc', 1000)->nullable();
            $table->string('bcc', 1000)->nullable();
            $table->mediumText('body_template')->nullable();
            $table->custom('is_deleted', 'tinyint(4)')->default(0);
            $table->custom('is_active', 'tinyint(4)')->default(0);
            $table->string('active_start_date', 45)->nullable();
            $table->string('active_end_date', 45)->nullable();
            $table->custom('is_protected', 'tinyint(4)')->default(0);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->integer('created_by')->nullable()->index('created_by');
            $table->integer('updated_by')->nullable()->index('updated_by');
            $table->string('parent_type')->nullable();
            $table->custom('parent_id', 'int(11)')->unsigned()->nullable()->index('product_id');
            $table->integer('day_offset')->default(0);
            $table->string('hint', 450)->nullable();
            $table->string('category', 100)->nullable();
        });

        Schema::create('expense', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('reimbursement_id')->unsigned();
            $table->string('class_code', 100)->nullable();
            $table->string('gl_code', 100)->nullable();
            $table->string('campaign_code', 100)->nullable();
            $table->string('description', 100)->nullable();
            $table->string('vendor', 450)->nullable();
            $table->dateTime('purchased_at')->nullable();
            $table->decimal('original_amount', 19, 4)->nullable();
            $table->decimal('requested_amount', 19, 4)->nullable();
            $table->decimal('approved_amount', 19, 4)->nullable();
            $table->string('storage_path', 450)->nullable();
            $table->string('tax_line1_name', 100)->nullable();
            $table->decimal('tax_line1_amount', 19, 4)->nullable();
            $table->string('tax_line2_name', 100)->nullable();
            $table->decimal('tax_line2_amount', 19, 4)->nullable();
            $table->string('tax_line3_name', 100)->nullable();
            $table->decimal('tax_line3_amount', 19, 4)->nullable();
            $table->string('tax_line4_name', 100)->nullable();
            $table->decimal('tax_line4_amount', 19, 4)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->integer('deleted_by')->unsigned()->nullable();
        });

        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->increments('id');
            $table->mediumText('connection');
            $table->mediumText('queue');
            $table->longText('payload');
            $table->longText('exception')->nullable()->charset('utf8')->collation('utf8_unicode_ci');
            $table->timestamp('failed_at')->nullable();
        });

        Schema::create('files', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('name');
            $table->string('filename')->index('filename');
            $table->string('content_type', 64)->nullable();
            $table->bigInteger('size')->unsigned();
            $table->string('_rackspace_uid');
            $table->boolean('_transfered')->nullable()->default(0);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
        });

        Schema::create('fundraising_page_members', function (Blueprint $table) {
            $table->custom('id', 'int(11)', true)->unsigned();
            $table->custom('fundraising_page_id', 'int(11)')->unsigned()->index('fundraising_page_id');
            $table->integer('member_id')->index('member_id');
            $table->decimal('amount_raised', 10, 4)->default(0.0000);
            $table->integer('donation_count')->default(0);
        });

        Schema::create('fundraising_page_reports', function (Blueprint $table) {
            $table->custom('id', 'int(11)', true)->unsigned();
            $table->custom('fundraising_page_id', 'int(11)')->unsigned()->index('fundraising_page_id');
            $table->integer('member_id')->nullable()->index('member_id');
            $table->string('reason', 200)->nullable();
            $table->dateTime('reported_at');
        });

        Schema::create('fundraising_pages', function (Blueprint $table) {
            $table->custom('id', 'int(11)', true)->unsigned();
            $table->integer('product_id')->index('product_id');
            $table->integer('member_organizer_id')->index('member_organizer_id');
            $table->string('status', 20)->default('draft');
            $table->string('privacy', 20)->default('private');
            $table->string('url', 500)->nullable();
            $table->string('title', 200)->nullable();
            $table->text('description', 65535)->nullable();
            $table->string('video_url', 512)->nullable();
            $table->string('category', 20)->nullable();
            $table->custom('photo_id', 'int(11)')->unsigned()->nullable()->index('photo_id');
            $table->date('goal_deadline')->nullable();
            $table->decimal('goal_amount', 10, 4)->nullable();
            $table->boolean('is_team')->default(0);
            $table->string('team_name', 200)->nullable();
            $table->custom('team_photo_id', 'int(11)')->unsigned()->nullable()->index('team_photo_id');
            $table->boolean('public_attributions_enabled')->default(0);
            $table->boolean('donor_comments_enabled')->default(0);
            $table->boolean('public_comments_enabled')->default(0);
            $table->boolean('allow_donations')->default(0);
            $table->decimal('amount_raised', 10, 4)->default(0.0000);
            $table->decimal('amount_raised_offset', 10, 4)->nullable()->default(0.0000);
            $table->integer('donation_count')->default(0);
            $table->integer('donation_count_offset')->nullable()->default(0);
            $table->integer('report_count')->default(0);
            $table->decimal('progress_percent', 8, 4)->default(0.0000);
            $table->string('thank_you_email_subject', 200)->nullable();
            $table->text('thank_you_email_body', 65535)->nullable();
            $table->date('activated_date')->nullable();
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
            $table->integer('created_by')->index('created_by');
            $table->integer('updated_by')->index('updated_by');
            $table->dateTime('deleted_at')->nullable();
            $table->integer('deleted_by')->nullable()->index('deleted_by');
        });

        Schema::create('group_account', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('group_id')->unsigned();
            $table->integer('account_id')->unsigned();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('order_item_id')->unsigned()->nullable();
            $table->string('source', 50)->nullable();
            $table->string('end_reason', 50)->nullable();
        });

        Schema::create('hook_deliveries', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('hook_id')->index('hook_id');
            $table->string('guid', 40);
            $table->mediumText('req_headers');
            $table->mediumText('req_body');
            $table->integer('res_status');
            $table->mediumText('res_headers');
            $table->mediumText('res_body');
            $table->custom('completed_in', 'float');
            $table->dateTime('delivered_at');
        });

        Schema::create('hooks', function (Blueprint $table) {
            $table->integer('id', true);
            $table->boolean('active')->unsigned()->default(0);
            $table->string('payload_url')->default('');
            $table->string('content_type', 128)->default('application/json');
            $table->string('secret')->nullable();
            $table->boolean('insecure_ssl')->unsigned()->default(0);
            $table->dateTime('created_at');
            $table->dateTime('updated_at');
        });

        Schema::create('images', function (Blueprint $table) {
            $table->integer('id', true);
            $table->custom('media_id', 'int(11)')->unsigned()->nullable()->index('media_id');
            $table->integer('parent_id')->nullable();
            $table->string('uid', 191)->index('uid')->comment('cloudfiles object name');
            $table->string('filename', 191)->default('')->index('filename');
            $table->string('extension', 32)->nullable();
            $table->integer('size')->nullable();
            $table->string('content_type', 64)->nullable();
            $table->string('public_url')->nullable();
            $table->timestamp('createddatetime')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->unique(['uid'], 'uid_unique');
        });

        Schema::create('imports', function (Blueprint $table) {
            $table->increments('id');
            $table->string('import_type', 45);
            $table->string('stage', 20)->default('analysis_queue');
            $table->string('name', 120)->nullable();
            $table->string('file_name', 520)->nullable();
            $table->string('file_path')->nullable();
            $table->boolean('is_complete')->default(0);
            $table->custom('total_records', 'int(10)')->nullable();
            $table->custom('current_record', 'int(10)')->default(0);
            $table->custom('added_records', 'int(10)')->nullable();
            $table->custom('updated_records', 'int(10)')->nullable();
            $table->custom('skipped_records', 'int(10)')->nullable();
            $table->custom('error_records', 'int(10)')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->text('import_messages', 65535)->nullable();
            $table->integer('import_messages_count')->default(0);
            $table->string('status_message', 500)->nullable();
            $table->dateTime('analysis_started_at')->nullable();
            $table->dateTime('analysis_ended_at')->nullable();
            $table->text('analysis_messages', 65535)->nullable();
            $table->integer('analyzed_ok_records')->default(0);
            $table->integer('analyzed_warning_records')->default(0);
        });

        Schema::create('jobs', function (Blueprint $table) {
            $table->bigInteger('id', true)->unsigned();
            $table->string('queue');
            $table->longText('payload');
            $table->custom('attempts', 'tinyint(3)')->unsigned();
            $table->integer('reserved_at')->unsigned()->nullable();
            $table->integer('available_at')->unsigned();
            $table->integer('created_at')->unsigned();
            $table->index(['queue', 'reserved_at']);
        });

        Schema::create('kiosk_sessions', function (Blueprint $table) {
            $table->increments('id');
            $table->custom('kiosk_id', 'int(11)')->unsigned()->index('kiosk_id');
            $table->integer('user_id')->index('user_id');
            $table->dateTime('last_activity')->nullable();
            $table->string('device_platform', 191);
            $table->string('device_uuid', 191)->nullable();
            $table->string('device_manufacturer', 191)->nullable();
            $table->string('device_model', 191)->nullable();
            $table->string('device_version', 191)->nullable();
            $table->string('ip', 39);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        Schema::create('kiosks', function (Blueprint $table) {
            $table->custom('id', 'int(11)', true)->unsigned();
            $table->boolean('enabled')->unsigned()->default(1);
            $table->string('name', 191);
            $table->integer('product_id')->index('product_id');
            $table->longText('config')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
        });

        Schema::create('layout', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('name', 45)->nullable();
            $table->longText('prepend')->nullable();
            $table->longText('append')->nullable();
            $table->string('variablename', 25)->nullable();
            $table->longText('css')->nullable();
            $table->boolean('isdefault')->unsigned()->default(0);
            $table->integer('created_by')->unsigned()->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
        });

        Schema::create('media', function (Blueprint $table) {
            $table->custom('id', 'int(11)', true)->unsigned();
            $table->custom('parent_id', 'int(11)')->unsigned()->nullable()->index('parent_id');
            $table->string('collection_name', 64);
            $table->string('name');
            $table->string('filename');
            $table->string('content_type', 64)->nullable();
            $table->custom('size', 'int(11)')->unsigned();
            $table->string('caption')->nullable();
            $table->custom('_image_id', 'int(11)')->unsigned()->nullable();
            $table->string('_rackspace_uid')->nullable();
            $table->boolean('_transfered')->unsigned()->nullable()->default(0);
            $table->timestamps();
            $table->integer('created_by');
            $table->integer('updated_by');
        });

        Schema::create('mediables', function (Blueprint $table) {
            $table->custom('media_id', 'int(11)')->unsigned();
            $table->custom('mediable_id', 'int(11)')->unsigned()->nullable();
            $table->string('mediable_type', 128);
            $table->unique(['media_id', 'mediable_id', 'mediable_type'], 'media_id_mediable_id_mediable_type');
        });

        Schema::create('member_login', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('member_id')->nullable()->index('member_id');
            $table->string('user_agent', 500)->nullable();
            $table->string('ip', 45)->nullable();
            $table->dateTime('login_at')->nullable();
            $table->integer('impersonated_by')->nullable();
        });

        Schema::create('member', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('display_name', 45)->nullable();
            $table->string('title', 45)->nullable();
            $table->string('first_name', 45)->nullable();
            $table->string('last_name', 45)->nullable();
            $table->string('email', 45)->nullable();
            $table->string('password', 500)->nullable();
            $table->string('remember_token')->nullable();
            $table->string('access', 500)->nullable()->default('member');
            $table->string('ship_title', 45)->nullable();
            $table->string('ship_first_name', 45)->nullable();
            $table->string('ship_last_name', 45)->nullable();
            $table->string('ship_organization_name', 45)->nullable();
            $table->string('ship_email', 45)->nullable();
            $table->string('ship_address_01', 450)->nullable();
            $table->string('ship_address_02', 450)->nullable();
            $table->string('ship_city', 200)->nullable();
            $table->string('ship_state', 45)->nullable();
            $table->string('ship_zip', 45)->nullable();
            $table->string('ship_country', 200)->nullable();
            $table->string('ship_phone', 45)->nullable();
            $table->string('bill_title', 45)->nullable();
            $table->string('bill_first_name', 45)->nullable();
            $table->string('bill_last_name', 45)->nullable();
            $table->string('bill_organization_name', 45)->nullable();
            $table->string('bill_email', 45)->nullable();
            $table->string('bill_address_01', 450)->nullable();
            $table->string('bill_address_02', 450)->nullable();
            $table->string('bill_city', 200)->nullable();
            $table->string('bill_state', 45)->nullable();
            $table->string('bill_zip', 45)->nullable();
            $table->string('bill_country', 200)->nullable();
            $table->string('bill_phone', 45)->nullable();
            $table->boolean('email_opt_in')->default(0);
            $table->boolean('is_active')->default(1);
            $table->integer('donor_id')->nullable();
            $table->boolean('force_password_reset')->default(0);
            $table->integer('account_type_id')->nullable()->index('account_type_id');
            $table->integer('_drop_membership_id')->nullable()->index('membership_id');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->integer('created_by')->nullable()->index('created_by');
            $table->integer('updated_by')->nullable()->index('updated_by');
            $table->integer('sync_status')->default(-1);
            $table->string('sign_up_method', 45)->nullable()->default('checkout');
            $table->dateTime('_drop_membership_expires_on')->nullable();
            $table->string('referral_source', 45)->nullable();
            $table->custom('nps', 'tinyint(2)')->nullable();
        });

        Schema::create('membership_access', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('membership_id')->nullable()->index('ix_membership_access_membership_id');
            $table->string('parent_type', 45)->nullable();
            $table->integer('parent_id')->nullable();
            $table->index(['parent_type', 'parent_id'], 'ix_membership_access_parent_type', 'HASH');
        });

        Schema::create('membership_promocodes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('membership_id')->unsigned();
            $table->string('promocode', 45)->nullable();
        });

        Schema::create('membership', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('sequence')->nullable();
            $table->string('name', 250)->nullable();
            $table->text('description', 65535)->nullable();
            $table->string('default_promo_code', 45)->nullable();
            $table->string('default_url', 500)->nullable();
            $table->string('renewal_url', 500)->nullable();
            $table->integer('days_to_expire')->nullable();
            $table->string('dp_id', 50)->nullable();
            $table->integer('created_by')->nullable()->index('created_by');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->integer('updated_by')->nullable()->index('updated_by');
            $table->dateTime('deleted_at')->nullable();
            $table->integer('deleted_by')->unsigned()->nullable();
            $table->dateTime('starts_at')->nullable();
            $table->boolean('should_display_badge')->default(0);
        });

        Schema::create('metadata', function (Blueprint $table) {
            $table->bigInteger('id', true)->unsigned();
            $table->string('metadatable_type');
            $table->bigInteger('metadatable_id')->unsigned();
            $table->string('key')->nullable();
            $table->string('type')->nullable();
            $table->longText('value')->nullable();
            $table->boolean('encrypted')->default(0);
            $table->timestamps();
            $table->index(['metadatable_id', DB::raw('`metadatable_type`(191)')], 'metadatable_id_metadatable_type');
        });

        Schema::create('node', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('parentid')->nullable()->index('parentid');
            $table->integer('sequence')->unsigned()->nullable();
            $table->string('title', 150)->nullable();
            $table->string('type', 20)->nullable();
            $table->integer('level')->unsigned()->nullable();
            $table->custom('isactive', 'int(1)')->unsigned()->nullable();
            $table->custom('ishidden', 'int(1)')->unsigned()->nullable();
            $table->string('url', 500)->nullable();
            $table->string('pagetitle', 150)->nullable();
            $table->string('target', 45)->nullable();
            $table->string('access_required', 500)->nullable();
            $table->boolean('requires_login')->unsigned()->default(0);
            $table->boolean('hide_menu_link_when_logged_out')->unsigned();
            $table->boolean('protected')->unsigned()->default(0);
            $table->string('code', 45)->nullable();
            $table->integer('category_id')->nullable();
            $table->integer('created_by')->unsigned()->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
        });

        Schema::create('nodecontent', function (Blueprint $table) {
            $table->integer('id', true);
            $table->longText('body')->nullable();
            $table->dateTime('createddatetime')->nullable();
            $table->integer('createdbyuserid')->index('createdbyuserid');
            $table->dateTime('modifieddatetime')->nullable();
            $table->integer('modifiedbyuserid')->index('modifiedbyuserid');
            $table->string('serverfile', 50)->nullable();
            $table->string('metadescription', 500)->nullable();
            $table->string('metakeywords', 500)->nullable();
            $table->integer('nodeid')->index('nodeid');
            $table->integer('layoutid')->nullable()->index('layoutid');
            $table->custom('featured_image_id', 'int(11)')->unsigned()->nullable()->index('featured_image_id');
            $table->custom('alt_image_id', 'int(11)')->unsigned()->nullable()->index('alt_image_id');
        });

        Schema::create('order_promocodes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order_id')->unsigned();
            $table->string('promocode', 45)->nullable();
        });

        Schema::create('password_resets', function (Blueprint $table) {
            $table->string('email')->index();
            $table->string('token')->index();
            $table->dateTime('created_at')->nullable();
        });

        Schema::create('payment_methods', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('member_id')->index('member_id')->comment('Member ID for the user');
            $table->string('processor', 32)->default('networkmerchants')->comment('Type of funding source, which is one of the following values: vault.');
            $table->string('status', 12)->default('ACTIVE');
            $table->string('display_name', 40)->default('')->comment('Display name of funding source.');
            $table->string('token', 40)->nullable()->comment('3rd-party token or ID. For example, a Vault ID.');
            $table->string('token_type', 40)->nullable();
            $table->boolean('use_as_default')->unsigned()->default(0)->comment('Flag used to default the default funding source.');
            $table->string('fingerprint', 40)->nullable();
            $table->string('account_type', 32)->nullable();
            $table->char('account_last_four', 4)->nullable()->comment('Last 4 digits or characters in account number.');
            $table->date('cc_expiry')->nullable();
            $table->string('ach_bank_name', 40)->nullable();
            $table->string('ach_account_type', 14)->nullable()->comment('The customer\'s ACH account type. Values: \'checking\' or \'savings\'.');
            $table->string('ach_entity_type', 14)->nullable()->comment('The customer\'s ACH account entity. Values: \'personal\' or \'business\'.');
            $table->string('ach_routing', 14)->nullable();
            $table->string('billing_first_name')->nullable()->comment('Cardholder\'s first name.');
            $table->string('billing_last_name')->nullable()->comment('Cardholder\'s last name.');
            $table->string('billing_email')->nullable();
            $table->string('billing_address1')->nullable()->comment('Cardholder\'s billing address.');
            $table->string('billing_address2')->nullable()->comment('Card billing address, line 2.');
            $table->string('billing_city')->nullable()->comment('Card billing city.');
            $table->string('billing_state')->nullable()->comment('Card billing state/province. Format: CC.');
            $table->string('billing_postal')->nullable()->comment('Card billing postal code.');
            $table->string('billing_country')->nullable()->comment('Card billing country code. Format: CC/ISO 3166.');
            $table->string('billing_phone')->nullable()->comment('Billing phone number.');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->dateTime('deleted_at')->nullable();
        });

        Schema::create('payment_option_group', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('name', 150)->nullable();
            $table->boolean('is_deleted')->default(0);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->integer('created_by')->nullable()->index('created_by');
            $table->integer('updated_by')->nullable()->index('updated_by');
        });

        Schema::create('payment_option', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('group_id')->nullable()->index('group_id');
            $table->integer('sequence')->nullable();
            $table->decimal('amount', 19, 4)->default(0.0000);
            $table->boolean('is_custom')->default(0);
            $table->boolean('is_recurring')->default(0);
            $table->integer('recurring_day')->nullable();
            $table->integer('recurring_day_of_week')->nullable();
            $table->decimal('recurring_amount_total', 19, 4)->nullable()->default(0.0000);
            $table->string('recurring_frequency', 50)->nullable();
            $table->boolean('recurring_with_dpo')->default(0);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->integer('created_by')->nullable()->index('created_by');
            $table->integer('updated_by')->nullable()->index('updated_by');
            $table->boolean('is_deleted')->default(0);
        });

        Schema::create('payments_pivot', function (Blueprint $table) {
            $table->bigInteger('id', true)->unsigned();
            $table->bigInteger('payment_id')->unsigned();
            $table->integer('order_id')->nullable()->index('order_id');
            $table->integer('recurring_payment_profile_id')->nullable()->index('recurring_payment_profile_id')->comment('Temporary column. Required until we eliminate rpps.');
            $table->integer('transaction_id')->nullable()->index('transaction_id')->comment('Temporary column. Required until we eliminate transactions.');
            $table->unique(['payment_id', 'order_id'], 'payment_id_order');
            $table->unique(['payment_id', 'recurring_payment_profile_id', 'transaction_id'], 'payment_id_transaction');
        });

        Schema::create('payments', function (Blueprint $table) {
            $table->bigInteger('id', true)->unsigned()->comment('Unique identifier for the object.');
            $table->enum('type', ['card', 'bank', 'paypal', 'cheque', 'cash', 'unknown']);
            $table->boolean('livemode')->unsigned()->comment('Indicates whether the payment exists in live mode.');
            $table->enum('status', ['succeeded', 'pending', 'failed'])->comment('The status of the payment.');
            $table->decimal('amount', 19, 4)->unsigned()->comment('Amount representing how much to charge.');
            $table->decimal('amount_refunded', 19, 4)->unsigned()->comment('Amount refunded (can be less than the amount attribute on the payment if a partial refund was issued).');
            $table->char('currency', 3)->comment('Three-letter ISO currency code.');
            $table->boolean('paid')->unsigned()->comment('If the payment succeeded, or was successfully authorized for later capture.');
            $table->boolean('captured')->unsigned()->comment('If the payment was created without capturing, this Boolean represents whether it is still uncaptured or has since been captured.');
            $table->dateTime('captured_at')->nullable();
            $table->boolean('refunded')->unsigned()->comment('Whether the payment has been fully refunded. If the payment is only partially refunded, this attribute will still be false.');
            $table->string('reference_number', 40)->nullable()->comment('Transaction ID on the gateway.');
            $table->text('description', 65535)->comment('Often useful for displaying to users.');
            $table->string('statement_descriptor', 25)->nullable()->comment('Extra information about a payment. This will appear on your customer\'s statement.');
            $table->string('dispute', 140)->nullable()->comment('Details about the dispute if the payment has been disputed.');
            $table->string('failure_code', 40)->nullable()->comment('Error code explaining reason for payment failure if available');
            $table->string('failure_message')->nullable()->comment('Message to user further explaining reason for payment failure if available.');
            $table->enum('outcome', ['authorized', 'manual_review', 'issuer_declined', 'blocked', 'invalid'])->comment('Details about whether the payment was accepted, and why.');
            $table->integer('source_account_id')->nullable()->index('source_account_id')->comment('The account that this source belongs to. ');
            $table->integer('source_payment_method_id')->nullable()->index('source_payment_method_id');
            $table->string('gateway_type', 40)->nullable()->default('NULL');
            $table->string('gateway_customer', 64)->nullable()->default('NULL')->comment('For Stripe a customer. For PaySafe a profile. For Vanco a customer.');
            $table->string('gateway_source', 40)->nullable()->default('NULL')->comment('For NMI a customer vault. For Stripe a card or bank account. For PayPal a billing agreement.');
            $table->enum('card_funding', ['credit', 'debit', 'prepaid', 'unknown'])->nullable()->comment('Card funding type.');
            $table->enum('card_brand', ['American Express', 'Carte Blanche', 'China UnionPay', 'Diners Club', 'Discover', 'Elo', 'JCB', 'Laser', 'Maestro', 'MasterCard', 'Solo', 'Switch', 'UnionPay', 'Visa', 'Unknown'])->nullable()->comment('Card brand.');
            $table->string('card_fingerprint', 40)->nullable()->comment('Uniquely identifies this particular card number.');
            $table->char('card_last4', 4)->nullable()->comment('The last four digits of the card.');
            $table->custom('card_exp_month', 'tinyint(2)')->unsigned()->nullable()->comment('Two-digit number representing the card’s expiration month.');
            $table->custom('card_exp_year', 'smallint(4)')->unsigned()->nullable()->comment('Four-digit number representing the card’s expiration year.');
            $table->enum('card_cvc_check', ['pass', 'fail', 'unavailable', 'unchecked'])->nullable()->comment('If a CVC was provided, results of the check.');
            $table->enum('card_tokenization_method', ['apple_pay', 'android_pay'])->nullable()->comment('If the card number is tokenized, this is the method that was used.');
            $table->enum('card_entry_type', ['mag_stripe_reader', 'integrated_circuit_card', 'card_not_present'])->nullable();
            $table->enum('card_verification', ['signature', 'pin', 'pin_signature', 'failed', 'not_required'])->nullable();
            $table->char('card_country', 2)->nullable()->comment('Two-letter ISO code representing the country of the card.');
            $table->string('card_name')->nullable()->comment('Cardholder name.');
            $table->string('card_address_line1')->nullable()->comment('Address line 1 (Street address/PO Box/Company name).');
            $table->enum('card_address_line1_check', ['pass', 'fail', 'unavailable', 'unchecked'])->nullable()->comment('Results of the check.');
            $table->string('card_address_line2')->nullable()->comment('Address line 2 (Apartment/Suite/Unit/Building)');
            $table->string('card_address_city')->nullable()->comment('City/District/Suburb/Town/Village.');
            $table->string('card_address_state')->nullable()->comment('State/County/Province/Region.');
            $table->string('card_address_zip')->nullable()->comment('ZIP or postal code.');
            $table->enum('card_address_zip_check', ['pass', 'fail', 'unavailable', 'unchecked'])->nullable()->comment('Results of the check.');
            $table->string('card_address_country')->nullable()->comment('Billing address country, if provided when creating card.');
            $table->string('bank_name', 40)->nullable()->comment('Name of the bank associated with the routing number.');
            $table->string('bank_fingerprint', 40)->nullable()->comment('Uniquely identifies this particular bank account.');
            $table->char('bank_last4', 4)->nullable()->comment('The last four digits of the bank account number.');
            $table->string('bank_account_holder_name')->nullable()->comment('The name of the person or business that owns the bank account.');
            $table->enum('bank_account_holder_type', ['individual', 'company'])->nullable()->comment('The type of entity that holds the bank account.');
            $table->enum('bank_account_type', ['checking', 'savings'])->nullable();
            $table->string('bank_routing_number', 40)->nullable()->comment('The routing transit number for the bank account.');
            $table->string('cheque_number', 40)->nullable();
            $table->date('cheque_date')->nullable();
            $table->longText('signature')->nullable();
            $table->longText('payment_audit_log')->nullable()->collation('utf8mb4_bin');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        Schema::create('pledgables', function (Blueprint $table) {
            $table->custom('pledge_id', 'int(11)')->unsigned();
            $table->custom('pledgable_id', 'int(11)')->unsigned();
            $table->string('pledgable_type', 64);
            $table->primary(['pledge_id', 'pledgable_id', 'pledgable_type'], 'composite_primary_key');
        });

        Schema::create('pledges', function (Blueprint $table) {
            $table->custom('id', 'int(11)', true)->unsigned();
            $table->integer('account_id');
            $table->decimal('total_amount', 12)->default(0.00);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->decimal('funded_amount', 12)->default(0.00);
            $table->integer('funded_count')->unsigned()->default(0);
            $table->decimal('funded_percent', 12)->default(0.00);
            $table->string('funded_status', 16)->nullable();
            $table->date('last_donation_date')->nullable();
            $table->decimal('last_donation_amount', 12)->nullable();
            $table->date('first_donation_date')->nullable();
            $table->decimal('first_donation_amount', 12)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->custom('created_by', 'int(11)')->unsigned()->nullable();
            $table->custom('updated_by', 'int(11)')->unsigned()->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->custom('deleted_by', 'int(11)')->unsigned()->nullable();
        });

        Schema::create('popup_logs', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('popup_id')->nullable()->index('ix_popup_logs_popup_id');
            $table->dateTime('logged_at')->nullable();
            $table->string('type', 15)->nullable()->index('ix_popup_logs_type');
            $table->string('ip_address', 45)->nullable();
        });

        Schema::create('popups', function (Blueprint $table) {
            $table->integer('id', true);
            $table->custom('is_deleted', 'tinyint(4)')->default(0)->index('ix_popup_is_deleted');
            $table->string('name', 125)->nullable();
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
            $table->text('html', 65535)->nullable()->comment('the popup content');
            $table->integer('timeout')->default(0)->comment('milliseconds before we show the popup');
            $table->integer('conversion_limit')->default(0)->comment('max conversions (ex: only capture 50 email addresses)');
            $table->text('email_response', 65535)->nullable()->comment('the text to be emailed');
            $table->integer('node_id')->nullable()->index('ix_popup_node_id');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->integer('created_by')->nullable()->index('created_by');
            $table->integer('updated_by')->nullable()->index('updated_by');
            $table->custom('is_enabled', 'tinyint(4)')->default(0);
            $table->text('success_message', 65535)->nullable();
        });

        Schema::create('post', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('url_slug', 150)->nullable();
            $table->string('name', 250)->nullable();
            $table->text('description', 65535)->nullable();
            $table->dateTime('modifieddatetime')->nullable();
            $table->integer('modifiedbyuserid')->index('modifiedbyuserid');
            $table->integer('type')->index('type');
            $table->dateTime('postdatetime')->nullable();
            $table->integer('isenabled')->unsigned()->nullable();
            $table->string('filepath', 500)->nullable();
            $table->custom('media_id', 'int(11)')->unsigned()->nullable()->index('media_id');
            $table->integer('sequence')->unsigned()->nullable();
            $table->longText('body')->nullable();
            $table->text('embedcode', 65535)->nullable();
            $table->text('tags', 65535)->nullable();
            $table->text('location', 65535)->nullable();
            $table->string('filepathname', 500)->nullable();
            $table->string('url', 500)->nullable();
            $table->string('fineprint', 500)->nullable();
            $table->dateTime('expirydatetime')->nullable();
            $table->string('misc1', 500)->nullable();
            $table->string('misc2', 500)->nullable();
            $table->string('misc3', 500)->nullable();
            $table->string('author', 500)->nullable();
            $table->string('length_formatted', 45)->nullable();
            $table->string('length_milliseconds', 45)->nullable();
            $table->string('feature_image')->nullable();
            $table->custom('featured_image_id', 'int(11)')->unsigned()->nullable()->index('featured_image_id');
            $table->string('alt_image')->nullable();
            $table->custom('alt_image_id', 'int(11)')->unsigned()->nullable()->index('alt_image_id');
            $table->integer('created_by')->unsigned()->nullable();
            $table->dateTime('created_at')->nullable();
            $table->index(['isenabled', 'postdatetime', 'expirydatetime'], 'isenabled_postdatetime_expirydatetime');
        });

        Schema::create('posttype', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('url_slug', 150)->nullable();
            $table->string('name', 45)->nullable();
            $table->string('sysname', 45)->nullable();
            $table->string('rss_link', 500)->nullable();
            $table->text('rss_copyright', 65535)->nullable();
            $table->text('rss_description', 65535)->nullable();
            $table->text('itunes_subtitle', 65535)->nullable();
            $table->string('itunes_author', 500)->nullable();
            $table->string('itunes_owner_name', 500)->nullable();
            $table->string('itunes_owner_email', 500)->nullable();
            $table->string('imagepath', 500)->nullable();
            $table->custom('media_id', 'int(11)')->unsigned()->nullable()->index('media_id');
            $table->string('itunes_category', 500)->nullable();
            $table->custom('isitunes', 'tinyint(3)')->unsigned()->default(0);
            $table->integer('created_by')->unsigned()->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
        });

        Schema::create('product', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('name', 750)->nullable();
            $table->longText('description')->nullable();
            $table->string('permalink')->nullable();
            $table->string('imgfull', 100)->nullable();
            $table->custom('media_id', 'int(11)')->unsigned()->nullable()->index('media_id');
            $table->decimal('saleprice', 19, 4)->nullable();
            $table->decimal('price', 19, 4)->nullable();
            $table->dateTime('createddatetime');
            $table->dateTime('modifieddatetime')->nullable();
            $table->boolean('isenabled')->unsigned()->nullable()->index('isenabled');
            $table->boolean('show_in_pos')->default(1);
            $table->integer('category')->nullable();
            $table->boolean('ismale')->unsigned()->nullable();
            $table->boolean('isfemale')->unsigned()->nullable();
            $table->integer('createdbyuserid')->index('createdbyuserid');
            $table->integer('modifiedbyuserid')->nullable()->index('modifiedbyuserid');
            $table->string('code', 45)->nullable();
            $table->string('sizes', 45)->nullable();
            $table->string('author', 250)->nullable();
            $table->boolean('isfeatured')->unsigned()->default(0)->index('isfeatured');
            $table->boolean('isnew')->unsigned()->default(0)->index('isnew');
            $table->string('meta1', 200)->nullable();
            $table->string('meta2', 200)->nullable();
            $table->string('meta3', 200)->nullable();
            $table->text('summary', 65535)->nullable();
            $table->boolean('isclearance')->unsigned()->default(0);
            $table->custom('isdonation', 'tinyint(3)')->unsigned()->default(0)->index('isdonation');
            $table->boolean('isrecurring')->unsigned()->default(0);
            $table->boolean('istribute')->unsigned()->default(0);
            $table->string('meta4', 200)->nullable();
            $table->string('meta5', 200)->nullable();
            $table->string('meta6', 200)->nullable();
            $table->boolean('isfblike')->unsigned()->default(1);
            $table->boolean('isfbcomment')->unsigned()->default(1);
            $table->decimal('goalamount', 19, 4)->nullable();
            $table->decimal('goal_progress_offset', 10, 4)->nullable();
            $table->date('goal_deadline')->nullable();
            $table->string('seo_pagetitle', 200)->nullable();
            $table->text('seo_pagekeywords', 65535)->nullable();
            $table->text('seo_pagedescription', 65535)->nullable();
            $table->custom('outofstock_allow', 'tinyint(4)')->default(1);
            $table->string('outofstock_message', 250)->nullable();
            $table->integer('limit_sales')->nullable();
            $table->string('meta7', 200)->nullable();
            $table->string('meta8', 200)->nullable();
            $table->string('meta9', 200)->nullable();
            $table->string('meta10', 200)->nullable();
            $table->string('meta11', 200)->nullable();
            $table->string('meta12', 200)->nullable();
            $table->string('meta13', 200)->nullable();
            $table->string('meta14', 200)->nullable();
            $table->string('meta15', 200)->nullable();
            $table->string('meta16', 200)->nullable();
            $table->string('meta17', 200)->nullable();
            $table->string('meta18', 200)->nullable();
            $table->string('meta19', 200)->nullable();
            $table->string('meta20', 200)->nullable();
            $table->string('meta21', 200)->nullable();
            $table->string('meta22', 200)->nullable();
            $table->string('meta23', 200)->nullable();
            $table->string('add_to_label', 200)->nullable();
            $table->string('alt_button_label', 150)->nullable();
            $table->string('alt_button_url', 550)->nullable();
            $table->boolean('ach_only')->default(0);
            $table->boolean('hide_qty')->default(0);
            $table->date('publish_start_date')->nullable();
            $table->date('publish_end_date')->nullable();
            $table->custom('goal_use_dpo', 'tinyint(4)')->default(1);
            $table->string('email_notify', 500)->nullable();
            $table->boolean('allow_check_in')->default(0);
            $table->boolean('recurring_with_dpo')->default(0);
            $table->string('recurring_type', 12)->nullable();
            $table->string('recurring_initial_charge', 12)->nullable();
            $table->string('recurringinterval', 25)->nullable();
            $table->boolean('is_deleted')->default(0);
            $table->integer('deleted_by')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->string('dpo_nocalc', 1)->nullable();
            $table->boolean('is_onepage')->default(0);
            $table->string('thank_you_url', 550)->nullable();
            $table->boolean('is_tax_receiptable')->default(0);
            $table->boolean('allow_tributes')->default(0);
            $table->text('tribute_type_ids', 65535)->nullable();
            $table->boolean('allow_tribute_notification')->default(0);
            $table->boolean('hide_price')->default(0);
            $table->decimal('min_price', 19, 4)->nullable();
            $table->string('taxcloud_tic_id', 10)->nullable();
            $table->text('notes', 65535)->nullable();
            $table->boolean('show_orders')->default(0);
            $table->boolean('allow_public_message')->default(0);
            $table->boolean('allow_fundraising_pages')->default(0);
            $table->string('fundraising_page_name', 200)->nullable();
            $table->text('fundraising_page_summary', 65535)->nullable();
            $table->text('thank_you_email_template', 65535)->nullable();
        });

        Schema::create('productaudioclip', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('sequence')->unsigned()->nullable();
            $table->string('name', 45)->nullable();
            $table->string('serverfile', 256)->nullable();
            $table->integer('productid')->index('productid');
        });

        Schema::create('productcategory', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('name', 250)->nullable();
            $table->mediumText('description')->nullable();
            $table->boolean('ismale')->nullable();
            $table->boolean('isfemale')->nullable();
            $table->integer('sequence')->unsigned()->nullable();
            $table->string('imageserverfile', 500)->nullable();
            $table->custom('media_id', 'int(11)')->unsigned()->nullable()->index('media_id');
            $table->integer('parent_id')->nullable()->index('parent_id');
            $table->string('url_name', 250)->nullable();
            $table->integer('created_by')->unsigned()->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
        });

        Schema::create('productcategorylink', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('productid')->index('productid');
            $table->integer('categoryid')->index('categoryid');
        });

        Schema::create('productfields', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('productid')->nullable()->index('productid');
            $table->string('type', 50)->nullable();
            $table->string('name', 250)->nullable();
            $table->custom('isrequired', 'tinyint(4)')->default(0);
            $table->integer('sequence')->nullable();
            $table->string('format', 50)->nullable();
            $table->text('options', 65535)->nullable();
            $table->string('map_to_product_meta', 25)->nullable();
            $table->text('body', 65535)->nullable();
            $table->text('hint', 65535)->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->integer('deleted_by')->nullable()->index('deleted_by');
        });

        Schema::create('productinventory', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('productid')->nullable()->index('productid');
            $table->string('variantname', 250)->nullable();
            $table->integer('quantity')->nullable();
            $table->decimal('price', 19, 4)->nullable();
            $table->dateTime('quantitymodifieddatetime')->nullable();
            $table->integer('quantitymodifiedbyuserid')->nullable()->index('quantitymodifiedbyuserid');
            $table->decimal('saleprice', 19, 4)->nullable();
            $table->integer('sequence')->nullable();
            $table->custom('quantityrestock', 'float')->nullable();
            $table->custom('isdefault', 'tinyint(4)')->default(0);
            $table->custom('isshippable', 'tinyint(4)')->default(1);
            $table->boolean('is_shipping_free')->default(0);
            $table->custom('is_deleted', 'tinyint(4)')->default(0)->index('is_deleted');
            $table->custom('weight', 'float')->nullable()->default(0);
            $table->integer('membership_id')->nullable()->index('membership_id');
            $table->decimal('cost', 19, 4)->nullable();
        });

        Schema::create('productinventoryfiles', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('fileid')->index('fid');
            $table->integer('inventoryid')->index('pfid');
            $table->string('description')->nullable();
            $table->integer('download_limit')->default(-1);
            $table->integer('address_limit')->default(-1)->comment('number of different ip addresses file can be downloaded from');
            $table->integer('expiry_time')->default(-1)->comment('number of seconds that a users download should expire');
        });

        Schema::create('productorder', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('client_uuid', 48)->nullable()->index('client_uuid');
            $table->boolean('is_processed')->unsigned()->default(0);
            $table->boolean('is_test')->unsigned()->default(0);
            $table->string('invoicenumber', 48)->nullable();
            $table->dateTime('createddatetime')->nullable();
            $table->dateTime('confirmationdatetime')->nullable();
            $table->string('confirmationnumber', 100)->nullable();
            $table->boolean('iscomplete')->unsigned()->nullable()->default(0);
            $table->boolean('isrecurring')->unsigned()->default(0);
            $table->boolean('istribute')->unsigned()->default(0);
            $table->integer('recurring_items')->default(0);
            $table->integer('download_items')->default(0);
            $table->integer('shippable_items')->default(0);
            $table->custom('total_qty', 'int(11)')->unsigned()->nullable()->default(0);
            $table->float('total_weight', 10, 0)->unsigned()->nullable()->default(0);
            $table->string('promocode', 50)->nullable();
            $table->decimal('discount', 19, 4)->default(0.0000);
            $table->decimal('subtotal', 19, 4)->default(0.0000);
            $table->decimal('taxtotal', 19, 4)->default(0.0000);
            $table->decimal('shipping_amount', 19, 4)->nullable()->default(0.0000);
            $table->decimal('admin_amount', 10, 4)->nullable()->default(0.0000);
            $table->decimal('totalamount', 19, 4)->nullable()->default(0.0000);
            $table->decimal('original_totalamount', 19, 4)->nullable();
            $table->decimal('total_savings', 19, 4)->default(0.0000);
            $table->boolean('is_pos')->unsigned()->default(0);
            $table->boolean('is_anonymous')->default(1);
            $table->string('payment_type', 50)->nullable();
            $table->string('check_number', 50)->nullable();
            $table->dateTime('check_date')->nullable();
            $table->decimal('check_amt', 19, 4)->nullable();
            $table->decimal('cash_received', 19, 4)->nullable();
            $table->decimal('cash_change', 19, 4)->nullable();
            $table->string('payment_other_reference', 25)->nullable();
            $table->text('payment_other_note', 65535)->nullable();
            $table->string('source', 50)->nullable();
            $table->custom('source_id', 'int(11)')->unsigned()->nullable();
            $table->integer('member_id')->nullable()->index('member_id');
            $table->integer('account_type_id')->nullable()->index('account_type_id');
            $table->string('billing_title', 20)->nullable();
            $table->string('billing_first_name', 64)->nullable();
            $table->string('billing_last_name', 64)->nullable();
            $table->string('billing_organization_name', 100)->nullable();
            $table->string('billingname', 80)->nullable();
            $table->string('billingemail', 60)->nullable();
            $table->string('billingaddress1', 100)->nullable();
            $table->string('billingaddress2', 100)->nullable();
            $table->string('billingcity', 40)->nullable();
            $table->string('billingstate', 40)->nullable();
            $table->string('billingzip', 20)->nullable();
            $table->string('billingcountry', 2)->nullable();
            $table->string('billingphone', 36)->nullable();
            $table->string('billing_name_on_account', 80)->nullable();
            $table->string('billingcardtype', 25)->nullable();
            $table->string('billingcardlastfour', 6)->nullable();
            $table->char('billing_card_expiry_month', 2)->nullable();
            $table->char('billing_card_expiry_year', 4)->nullable();
            $table->custom('auth_attempts', 'tinyint(3)')->nullable()->unsigned()->default(0);
            $table->string('response_text')->nullable();
            $table->string('vault_id', 20)->nullable();
            $table->string('shipping_title', 20)->nullable();
            $table->string('shipping_first_name', 64)->nullable();
            $table->string('shipping_last_name', 64)->nullable();
            $table->string('shipping_organization_name', 100)->nullable();
            $table->string('shipname', 80)->nullable();
            $table->string('shipemail', 60)->nullable();
            $table->string('shipaddress1', 100)->nullable();
            $table->string('shipaddress2', 100)->nullable();
            $table->string('shipcity', 40)->nullable();
            $table->string('shipstate', 40)->nullable();
            $table->string('shipzip', 20)->nullable();
            $table->string('shipcountry', 2)->nullable();
            $table->string('shipphone', 36)->nullable();
            $table->boolean('is_free_shipping')->unsigned()->default(0);
            $table->string('tax_address1', 100)->nullable();
            $table->string('tax_address2', 100)->nullable();
            $table->string('courier_method', 120)->nullable();
            $table->string('pledge_interval', 50)->nullable();
            $table->string('pledge_day', 10)->nullable();
            $table->string('tax_city', 40)->nullable();
            $table->string('tax_state', 5)->nullable();
            $table->string('tax_zip', 12)->nullable();
            $table->string('tax_country', 5)->nullable();
            $table->text('status', 65535)->nullable();
            $table->boolean('email_opt_in')->default(0);
            $table->string('alt_contact_id', 10)->nullable();
            $table->string('alt_transaction_id', 200)->nullable();
            $table->boolean('dp_sync_order')->unsigned()->default(0);
            $table->string('client_ip', 40)->nullable();
            $table->char('ip_country', 2)->nullable();
            $table->mediumText('client_browser')->nullable();
            $table->string('ua_browser', 32)->nullable();
            $table->string('ua_browser_version', 12)->nullable();
            $table->string('ua_device_brand', 32)->nullable();
            $table->string('ua_device_model', 32)->nullable();
            $table->string('ua_os', 32)->nullable();
            $table->string('ua_os_version', 12)->nullable();
            $table->string('ga_client_id', 40)->nullable()->default('NULL');
            $table->dateTime('started_at')->nullable();
            $table->integer('shipping_method_id')->nullable()->index('shipping_method_id');
            $table->integer('created_by')->unsigned()->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->dateTime('voided_at')->nullable();
            $table->integer('voided_by')->nullable();
            $table->string('voided_reason')->nullable();
            $table->decimal('refunded_amt', 19, 4)->nullable();
            $table->string('refunded_auth', 50)->nullable();
            $table->dateTime('refunded_at')->nullable();
            $table->integer('refunded_by')->unsigned()->nullable();
            $table->dateTime('payment_lock_at')->nullable();
            $table->dateTime('ordered_at')->nullable()->index('ordered_at');
            $table->dateTime('alt_data_updated_at')->nullable();
            $table->integer('alt_data_updated_by')->unsigned()->nullable();
            $table->string('referral_source', 45)->nullable();
            $table->string('http_referer', 512)->nullable();
            $table->string('tracking_source', 50)->nullable();
            $table->string('tracking_medium', 50)->nullable();
            $table->string('tracking_campaign', 50)->nullable();
            $table->string('tracking_term', 50)->nullable();
            $table->string('tracking_content', 50)->nullable();
            $table->text('comments', 65535)->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->integer('deleted_by')->nullable();
        });

        Schema::create('productorderitem', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('productorderid')->index('productorderid');
            $table->integer('DELETE_productid')->nullable()->index('DELETE_productid');
            $table->integer('qty')->unsigned()->nullable();
            $table->decimal('price', 19, 4)->nullable();
            $table->decimal('discount', 19, 4)->default(0.0000);
            $table->decimal('original_price', 19, 4)->nullable();
            $table->integer('locked_to_item_id')->nullable()->index('locked_to_item_id');
            $table->string('promocode', 50)->nullable();
            $table->integer('productinventoryid')->nullable()->index('productinventoryid');
            $table->integer('recurring_day')->nullable();
            $table->integer('recurring_day_of_week')->nullable();
            $table->decimal('recurring_amount', 19, 4)->default(0.0000);
            $table->decimal('recurring_amount_total', 19, 4)->default(0.0000);
            $table->string('recurring_frequency', 50)->nullable();
            $table->boolean('recurring_with_dpo')->default(0);
            $table->boolean('recurring_with_initial_charge')->nullable();
            $table->string('tribute_name', 500)->nullable();
            $table->integer('dpo_tribute_id')->nullable();
            $table->integer('sponsorship_id')->nullable()->index('sponsorship_id');
            $table->boolean('sponsorship_is_expired')->unsigned()->default(0);
            $table->dateTime('sponsorship_expired_at')->nullable();
            $table->text('sponsorship_expired_reason', 65535)->nullable();
            $table->boolean('is_tribute')->default(0);
            $table->integer('tribute_type_id')->unsigned()->nullable()->index('tribute_type_id');
            $table->string('tribute_notify', 25)->nullable();
            $table->date('tribute_notify_at')->nullable();
            $table->text('tribute_message', 65535)->nullable();
            $table->string('tribute_notify_name', 150)->nullable();
            $table->string('tribute_notify_email', 150)->nullable();
            $table->string('tribute_notify_address', 250)->nullable();
            $table->string('tribute_notify_city', 100)->nullable();
            $table->string('tribute_notify_state', 4)->nullable();
            $table->string('tribute_notify_zip', 15)->nullable();
            $table->string('tribute_notify_country', 2)->nullable();
            $table->string('alt_transaction_id', 10)->nullable();
            $table->integer('original_variant_id')->nullable()->index('original_variant_id');
            $table->text('public_message', 65535)->nullable();
            $table->custom('fundraising_page_id', 'int(11)')->unsigned()->nullable()->index('fundraising_page_id');
            $table->integer('fundraising_member_id')->nullable()->index('fundraising_member_id');
        });

        Schema::create('productorderitemfield', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('orderitemid')->nullable()->index('orderitemid');
            $table->integer('fieldid')->nullable()->index('fieldid');
            $table->text('value', 65535)->nullable();
            $table->text('original_value', 65535)->nullable();
        });

        Schema::create('productorderitemfiles', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('fileid')->index('fid');
            $table->integer('orderitemid')->index('uid');
            $table->custom('granted', 'timestamp on update CURRENT_TIMESTAMP')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->integer('expiration')->default(-1);
            $table->smallInteger('accessed')->unsigned()->default(0);
            $table->mediumText('addresses')->nullable();
            $table->integer('download_limit')->default(-1);
            $table->integer('address_limit')->default(-1);
        });

        Schema::create('productorderitemtax', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('orderitemid')->index('orderitemid');
            $table->integer('taxid')->index('taxid');
            $table->decimal('amount', 19, 4)->nullable();
        });

        Schema::create('productpromocode', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('code', 15)->nullable();
            $table->text('description', 65535)->nullable();
            $table->decimal('discount', 19, 4)->unsigned()->nullable();
            $table->dateTime('startdate')->nullable();
            $table->dateTime('enddate')->nullable();
            $table->integer('allocation_limit')->unsigned()->nullable();
            $table->integer('buy_quantity')->unsigned()->nullable();
            $table->integer('usage_limit')->unsigned()->nullable();
            $table->integer('usage_limit_per_account')->unsigned()->nullable();
            $table->integer('usage_count')->unsigned()->default(0);
            $table->integer('createdbyuserid')->index('createdbyuserid');
            $table->dateTime('createddatetime')->nullable();
            $table->integer('modifiedbyuserid')->index('modifiedbyuserid');
            $table->dateTime('modifieddatetime')->nullable();
            $table->boolean('is_free_shipping')->default(0);
            $table->string('free_shipping_label', 25)->nullable();
            $table->string('discount_type', 15)->default('percent');
        });

        Schema::create('productpromocodecategory', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('promocodeid')->index('promocodeid');
            $table->integer('categoryid')->index('categoryid');
        });

        Schema::create('productpromocodeproduct', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('promocodeid')->index('promocodeid');
            $table->integer('productid')->index('productid');
        });

        Schema::create('producttax', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('code')->nullable();
            $table->text('description', 65535)->nullable();
            $table->custom('rate', 'float')->nullable();
            $table->text('city', 65535)->nullable()->comment('carriage return delimited list of city names to compare against during checkout to apply this tax');
            $table->integer('createdbyuserid')->index('createdbyuserid');
            $table->dateTime('createddatetime')->nullable();
            $table->integer('modifiedbyuserid')->index('modifiedbyuserid');
            $table->dateTime('modifieddatetime')->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->integer('deleted_by')->nullable()->index('deleted_by');
        });

        Schema::create('producttaxproduct', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('productid')->index('productid');
            $table->integer('taxid')->index('taxid');
        });

        Schema::create('producttaxregion', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('taxid')->index('taxid');
            $table->integer('regionid')->index('regionid');
        });

        Schema::create('recurring_payment_profiles', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('status', 14)->comment('Status of the recurring payment profile. It is one of the following values: Active, Pending, Cancelled, Suspended, Expired.');
            $table->boolean('is_manual')->default(0);
            $table->boolean('is_locked')->default(0);
            $table->string('profile_id', 14)->nullable()->comment('A unique identifier for future reference to the details of this recurring payment.');
            $table->integer('member_id')->index('member_id')->comment('The Member ID of the member owning the profile.');
            $table->string('subscriber_name', 32)->comment('Full name of the person receiving the product or service paid for by the recurring payment.');
            $table->dateTime('profile_start_date')->comment('The date when billing for this profile begins.');
            $table->string('profile_reference', 128)->comment('The merchant\'s own unique reference or invoice number.');
            $table->string('description', 128)->comment('Description of the recurring payment.');
            $table->dateTime('final_payment_due_date')->nullable()->comment('Final scheduled payment due date before the profile expires.');
            $table->decimal('aggregate_amount', 19, 4)->default(0.0000)->comment('Total amount collected thus far for scheduled payments.');
            $table->custom('max_failed_payments', 'tinyint(4)')->default(1)->comment('Number of scheduled payments that can fail before the profile is automatically suspended.');
            $table->boolean('auto_bill_out_amt')->default(0)->comment('Indicates whether you would like Donorshops to automatically bill the outstanding balance amount in the next billing cycle.');
            $table->decimal('nsf_fee', 19, 4)->default(0.0000)->comment('NSF Fee to charge for failed payments.');
            $table->string('ship_to_name', 32)->nullable()->comment('Person\'s name associated with this shipping address.');
            $table->string('ship_to_street', 100)->nullable()->comment('First street address.');
            $table->string('ship_to_street2', 100)->nullable()->comment('Second street address.');
            $table->string('ship_to_city', 40)->nullable()->comment('Name of city.');
            $table->string('ship_to_state', 40)->nullable()->comment('State or province.');
            $table->string('ship_to_zip', 20)->nullable()->comment('U.S. ZIP code or other country-specific postal code.');
            $table->string('ship_to_country', 2)->nullable()->comment('Country code.');
            $table->string('ship_to_phone_num', 20)->nullable()->comment('Phone number.');
            $table->string('transaction_type', 20)->default('Standard')->comment('The type of transaction. It is one of the following values: Standard, Donation.');
            $table->string('billing_period', 10)->default('Month')->comment('Unit for billing during this subscription period. It is one of the following values: Day, Week, SemiMonth, Month.');
            $table->custom('billing_frequency', 'tinyint(4)')->default(12)->comment('Number of billing periods that make up one billing cycle.');
            $table->integer('total_billing_cycles')->default(0)->comment('Number of billing cycles for payment period.');
            $table->decimal('amt', 19, 4)->comment('Billing amount for each billing cycle during this payment period. This amount does not include shipping and tax amounts.');
            $table->char('currency_code', 3)->default('USD')->comment('Currency code (default is USD).');
            $table->decimal('shipping_amt', 19, 4)->default(0.0000)->comment('Shipping amount for each billing cycle during this payment period.');
            $table->decimal('tax_amt', 19, 4)->default(0.0000)->comment('Tax amount for each billing cycle during this payment period.');
            $table->decimal('init_amt', 19, 4)->default(0.0000)->comment('Initial non-recurring payment amount due immediately upon profile creation.');
            $table->date('next_billing_date')->comment('The next scheduled billing date.');
            $table->date('next_attempt_date')->nullable()->comment('The next scheduled attempt to re-bill a failed payment.');
            $table->smallInteger('num_cycles_completed')->default(0)->comment('The number of billing cycles completed in the current active subscription period.');
            $table->smallInteger('num_cycles_remaining')->nullable()->comment('The number of billing cycles remaining in the current active subscription period.');
            $table->decimal('outstanding_balance', 19, 4)->default(0.0000)->comment('The current past due or outstanding balance for this profile.');
            $table->smallInteger('failed_payment_count')->default(0)->comment('The total number of failed billing cycles for this profile.');
            $table->dateTime('last_payment_date')->nullable()->comment('The date of the last successful payment received for this profile.');
            $table->decimal('last_payment_amt', 19, 4)->nullable()->comment('The amount of the last successful payment received for this profile.');
            $table->integer('payment_method_id')->nullable()->index('funding_source_id')->comment('The funding source to use in place of the default funding source.');
            $table->integer('productorder_id')->index('productorder_id')->comment('The Order ID of the original Order.');
            $table->integer('productorderitem_id')->index('productorderitem_id')->comment('The Order Line Item ID in the original Order.');
            $table->integer('productinventory_id')->nullable()->index('productinventory_id')->comment('The Product Inventory ID of the ordered Product.');
            $table->integer('product_id')->nullable()->index('product_id')->comment('The Product ID of the ordered Product.');
            $table->boolean('payment_mutex')->default(0)->comment('Mutex used while processing a recurring payment.');
            $table->integer('sponsorship_id')->unsigned()->nullable();
        });

        Schema::create('refunds', function (Blueprint $table) {
            $table->bigInteger('id', true)->unsigned()->comment('Unique identifier for the object.');
            $table->bigInteger('payment_id')->unsigned()->index('payment_id')->comment('ID of the payment that was refunded.');
            $table->enum('status', ['succeeded', 'failed', 'pending', 'canceled'])->comment('Status of the refund.');
            $table->string('reference_number', 40)->comment('This is the transaction number that appears on email receipts sent for this refund.');
            $table->decimal('amount', 19, 4);
            $table->char('currency', 3)->comment('Three-letter ISO currency code.');
            $table->integer('refunded_by_id')->index('refunded_by');
            $table->enum('reason', ['duplicate', 'fraudulent', 'requested_by_customer'])->comment('Reason for the refund.');
            $table->enum('failure_reason', ['lost_or_stolen_card', 'expired_or_canceled_card', 'unknown'])->nullable()->comment('If the refund failed, the reason for refund failure if known.');
            $table->longText('refund_audit_log')->nullable()->collation('utf8mb4_bin');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        Schema::create('region', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('code', 5)->nullable();
            $table->string('name', 250)->nullable();
            $table->string('country', 5)->nullable();
        });

        Schema::create('reimbursement', function (Blueprint $table) {
            $table->increments('id');
            $table->string('status', 50)->nullable();
            $table->string('payment_method', 50)->nullable();
            $table->string('justification', 1500)->nullable();
            $table->string('comments', 1500)->nullable();
            $table->decimal('total_original_amount', 19, 4)->nullable();
            $table->decimal('total_requested_amount', 19, 4)->nullable();
            $table->decimal('total_approved_amount', 19, 4)->nullable();
            $table->integer('expenses_count')->nullable();
            $table->dateTime('requested_on')->nullable();
            $table->dateTime('reimbursed_on')->nullable();
            $table->string('reimbursed_method', 45)->nullable();
            $table->string('reimbursed_reference', 45)->nullable();
            $table->string('first_name', 45)->nullable();
            $table->string('last_name', 45)->nullable();
            $table->string('email', 45)->nullable();
            $table->string('address_01', 450)->nullable();
            $table->string('address_02', 450)->nullable();
            $table->string('city', 200)->nullable();
            $table->string('state', 45)->nullable();
            $table->string('zip', 45)->nullable();
            $table->string('country', 200)->nullable();
            $table->string('phone', 45)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->integer('deleted_by')->unsigned()->nullable();
        });

        Schema::create('segment_items', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('segment_id')->nullable()->index('segment_id');
            $table->string('name', 150)->nullable();
            $table->string('summary', 2500)->nullable();
            $table->decimal('latitude', 9, 6)->nullable();
            $table->decimal('longitude', 9, 6)->nullable();
            $table->boolean('is_deleted')->default(0);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->integer('created_by')->nullable()->index('created_by');
            $table->integer('updated_by')->nullable()->index('updated_by');
            $table->string('link', 500)->nullable();
            $table->string('target', 20)->nullable();
        });

        Schema::create('segments', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('name', 150)->nullable();
            $table->string('name_plural', 152)->nullable();
            $table->text('description', 65535)->nullable();
            $table->boolean('is_text_only')->default(0);
            $table->boolean('is_simple')->default(0);
            $table->boolean('is_geographic')->default(0);
            $table->boolean('is_deleted')->default(0);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->integer('created_by')->nullable()->index('created_by');
            $table->integer('updated_by')->nullable()->index('updated_by');
            $table->integer('sequence')->nullable()->default(0);
            $table->boolean('show_as_filter')->default(1);
            $table->boolean('show_in_detail')->default(1);
        });

        Schema::create('settings', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('theme_id')->nullable();
            $table->string('name');
            $table->longText('value')->nullable();
            $table->string('label')->nullable();
            $table->string('type', 32)->nullable();
            $table->string('info')->nullable();
            $table->string('category', 64)->nullable();
        });

        Schema::create('shipping_method', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('name', 150)->nullable();
            $table->string('code', 45)->nullable();
            $table->string('description', 2500)->nullable();
            $table->custom('show_on_web', 'tinyint(4)')->default(1);
            $table->integer('created_by')->nullable()->index('created_by');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->integer('updated_by')->nullable()->index('updated_by');
            $table->custom('is_default', 'tinyint(4)')->default(0);
            $table->dateTime('deleted_at')->nullable();
            $table->integer('deleted_by')->nullable()->index('deleted_by');
            $table->string('countries', 2500)->nullable();
            $table->string('regions', 2500)->nullable();
            $table->integer('priority')->nullable();
        });

        Schema::create('shipping_tier', function (Blueprint $table) {
            $table->integer('id', true);
            $table->decimal('min_value', 19, 4)->default(0.0000);
            $table->decimal('max_value', 19, 4)->default(0.0000);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->integer('created_by')->nullable()->index('created_by');
            $table->integer('updated_by')->nullable()->index('updated_by');
        });

        Schema::create('shipping_value', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('method_id')->nullable()->index('method_id');
            $table->integer('tier_id')->nullable()->index('tier_id');
            $table->decimal('amount', 19, 4)->default(0.0000);
        });

        Schema::create('sponsors', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('member_id')->unsigned();
            $table->integer('sponsorship_id')->unsigned();
            $table->integer('order_item_id')->unsigned()->nullable();
            $table->string('source', 150)->nullable();
            $table->dateTime('started_at')->nullable();
            $table->integer('started_by')->unsigned()->nullable();
            $table->dateTime('ended_at')->nullable();
            $table->integer('ended_by')->unsigned()->nullable();
            $table->string('ended_reason', 150)->nullable();
            $table->string('ended_note', 2500)->nullable();
            $table->dateTime('last_payment_at')->nullable();
            $table->decimal('last_payment_amt', 19, 4)->nullable();
            $table->string('last_payment_status', 150)->nullable();
            $table->decimal('lifetime_amt', 19, 4)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->integer('deleted_by')->unsigned()->nullable();
        });

        Schema::create('sponsorship_payment_option_groups', function (Blueprint $table) {
            $table->integer('sponsorship_id')->unsigned();
            $table->integer('payment_option_group_id')->unsigned();
            $table->primary(['sponsorship_id', 'payment_option_group_id'], 'composite_primary_key');
        });

        Schema::create('sponsorship_segments', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('sponsorship_id')->nullable()->index('sponsorship_id');
            $table->integer('segment_id')->nullable()->index('segment_id');
            $table->integer('segment_item_id')->nullable()->index('segment_item_id');
            $table->string('value', 450)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->integer('created_by')->nullable()->index('created_by');
            $table->integer('updated_by')->nullable()->index('updated_by');
        });

        Schema::create('sponsorship', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('first_name', 45)->nullable();
            $table->string('last_name', 45)->nullable();
            $table->integer('_drop_payment_option_group_id')->nullable()->index('payment_option_group_id');
            $table->mediumText('private_notes')->nullable();
            $table->string('biography', 8000)->nullable();
            $table->string('image', 250)->nullable();
            $table->custom('media_id', 'int(11)')->unsigned()->nullable()->index('media_id');
            $table->string('street_number', 45)->nullable();
            $table->string('street_name', 250)->nullable();
            $table->string('village', 250)->nullable();
            $table->string('region', 250)->nullable();
            $table->string('country', 250)->nullable();
            $table->string('project', 250)->nullable();
            $table->date('birth_date')->nullable();
            $table->date('enrollment_date')->nullable();
            $table->string('school', 250)->nullable();
            $table->char('gender', 1)->nullable()->default('M');
            $table->decimal('longitude', 9, 6)->nullable();
            $table->decimal('latitude', 9, 6)->nullable();
            $table->date('created_at')->nullable();
            $table->integer('created_by')->nullable()->index('created_by');
            $table->date('updated_at')->nullable();
            $table->integer('updated_by')->nullable()->index('updated_by');
            $table->boolean('is_deleted')->default(0);
            $table->string('phone', 45)->nullable();
            $table->boolean('is_enabled')->default(0);
            $table->string('reference_number', 25)->nullable();
            $table->string('meta1', 200)->nullable();
            $table->string('meta2', 200)->nullable();
            $table->string('meta3', 200)->nullable();
            $table->string('meta4', 200)->nullable();
            $table->string('meta5', 200)->nullable();
            $table->string('meta6', 200)->nullable();
            $table->string('meta7', 200)->nullable();
            $table->string('meta8', 200)->nullable();
            $table->string('meta9', 200)->nullable();
            $table->string('meta10', 200)->nullable();
            $table->string('meta11', 200)->nullable();
            $table->string('meta12', 200)->nullable();
            $table->string('meta13', 200)->nullable();
            $table->string('meta14', 200)->nullable();
            $table->string('meta15', 200)->nullable();
            $table->string('meta16', 200)->nullable();
            $table->string('meta17', 200)->nullable();
            $table->string('meta18', 200)->nullable();
            $table->string('meta19', 200)->nullable();
            $table->string('meta20', 200)->nullable();
            $table->string('meta21', 200)->nullable();
            $table->string('meta22', 200)->nullable();
            $table->string('meta23', 200)->nullable();
            $table->boolean('is_sponsored')->default(0);
            $table->integer('sponsor_count')->default(0);
            $table->boolean('is_sponsored_auto')->default(1);
            $table->dateTime('last_timeline_update_on')->nullable();
        });

        Schema::create('sql_migrations', function (Blueprint $table) {
            $table->string('migration');
            $table->integer('batch');
        });

        Schema::create('taggables', function (Blueprint $table) {
            $table->custom('tag_id', 'int(11)')->unsigned();
            $table->custom('taggable_id', 'int(11)')->unsigned()->nullable();
            $table->string('taggable_type', 128);
            $table->unique(['tag_id', 'taggable_id', 'taggable_type'], 'tag_id_taggable_id_taggable_type');
        });

        Schema::create('tags', function (Blueprint $table) {
            $table->custom('id', 'int(11)', true)->unsigned();
            $table->string('name', 191)->default('')->unique('name');
            $table->timestamps();
            $table->integer('created_by');
            $table->integer('updated_by');
        });

        Schema::create('tax_receipts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order_id')->nullable()->index('order_id');
            $table->integer('transaction_id')->nullable()->index('transaction_id');
            $table->string('number', 50)->nullable();
            $table->dateTime('issued_at')->nullable();
            $table->decimal('amount', 19, 4)->nullable();
            $table->string('name')->nullable();
            $table->string('first_name', 45)->nullable();
            $table->string('last_name', 45)->nullable();
            $table->string('email', 45)->nullable();
            $table->string('address_01', 450)->nullable();
            $table->string('address_02', 450)->nullable();
            $table->string('city', 200)->nullable();
            $table->string('state', 45)->nullable();
            $table->string('zip', 45)->nullable();
            $table->string('country', 200)->nullable();
            $table->string('phone', 45)->nullable();
            $table->string('storage_path', 450)->nullable();
            $table->longText('changes')->nullable();
            $table->longText('versions')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->integer('deleted_by')->unsigned()->nullable();
        });

        Schema::create('templates', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('theme_id')->nullable();
            $table->string('type', 100)->nullable();
            $table->string('name', 100)->nullable();
            $table->mediumText('content')->nullable();
            $table->mediumText('content_draft')->nullable();
            $table->mediumText('content_reset')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->custom('is_shortcode', 'tinyint(4)')->default(0);
            $table->string('shortcode_params', 5000)->nullable();
        });

        Schema::create('ticket_check_in', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('order_id')->nullable()->index('order_id');
            $table->integer('order_item_id')->nullable()->index('order_item_id');
            $table->dateTime('check_in_at')->nullable();
            $table->integer('check_in_by')->nullable()->index('check_in_by');
        });

        Schema::create('timelines', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('parent_type', 55)->nullable();
            $table->custom('parent_id', 'int(11)')->unsigned()->nullable();
            $table->string('tag', 45)->nullable();
            $table->custom('is_private', 'tinyint(4)')->nullable()->default(0);
            $table->date('posted_on')->nullable();
            $table->string('headline', 500)->nullable();
            $table->string('message', 5000)->nullable();
            $table->string('attachments', 5000)->nullable();
            $table->integer('attachment_size')->nullable();
            $table->longText('data')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->custom('created_by', 'int(11)')->unsigned()->nullable();
            $table->custom('updated_by', 'int(11)')->unsigned()->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->custom('deleted_by', 'int(11)')->unsigned()->nullable();
        });

        Schema::create('transaction_fees', function (Blueprint $table) {
            $table->bigInteger('id', true)->unsigned();
            $table->char('period', 7)->index('period');
            $table->enum('status', ['pending', 'billed'])->default('pending');
            $table->enum('source_type', ['payment', 'refund']);
            $table->bigInteger('source_id')->unsigned();
            $table->decimal('source_amount', 19, 4);
            $table->decimal('rate', 5, 4)->unsigned();
            $table->decimal('amount', 19, 4);
            $table->char('currency', 3);
            $table->decimal('exchange_rate', 19, 6)->unsigned();
            $table->decimal('settlement_amount', 19, 4);
            $table->char('settlement_currency', 3);
            $table->string('description')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->unique(['source_type', 'source_id'], 'type_source');
        });

        Schema::create('transactions', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('transaction_id', 18)->nullable()->comment('Unique transaction ID of the payment.');
            $table->string('parent_transaction_id', 18)->nullable()->comment('Parent or related transaction identification number.');
            $table->string('reciept_id', 16)->nullable()->comment('Receipt identification number.');
            $table->boolean('dp_auto_sync')->default(0);
            $table->string('dpo_gift_id', 18)->nullable()->comment('Gift identification number returned by DPO.');
            $table->string('dpo_refund_gift_id', 18)->nullable();
            $table->string('transaction_status', 16)->default('New')->comment('The status of the transaction. It is one of the following values: New, Active, Error, Completed');
            $table->string('transaction_type', 16)->comment('The type of transaction. It is one of the following values: Cart, Recurring.');
            $table->integer('recurring_payment_profile_id')->nullable()->index('recurring_payment_profile_id');
            $table->integer('payment_method_id')->nullable()->index('funding_source_id')->comment('The funding source used.');
            $table->string('payment_method_type', 32);
            $table->string('payment_method_desc')->index('payment_method');
            $table->dateTime('order_time')->comment('Time/date stamp of payment.');
            $table->decimal('amt', 19, 4)->comment('The final amount charged, including any shipping and taxes.');
            $table->char('currency_code', 3)->default('USD')->comment('A 3-character currency code.');
            $table->decimal('tax_amt', 19, 4)->default(0.0000)->comment('Tax charged on the transaction.');
            $table->decimal('shipping_amt', 19, 4)->default(0.0000)->comment('Tax charged on the transaction.');
            $table->string('payment_status', 24)->default('None')->comment('Status of the payment. It is one of the following values: None, Cancel-Reversal, Completed, Denied, Expired, Failed, In-Progress, Partially-Refunded, Pending, Refunded, Reversed, Processed, Voided.');
            $table->string('pending_reason', 128)->nullable()->comment('The reason the payment is pending.');
            $table->string('reason_code', 128)->nullable()->comment('The reason for a reversal if the transaction type is reversal.');
            $table->string('ship_to_name', 32)->nullable()->comment('Person\'s name associated with this shipping address.');
            $table->string('ship_to_street', 100)->nullable()->comment('First street address.');
            $table->string('ship_to_street2', 100)->nullable()->comment('Second street address.');
            $table->string('ship_to_city', 40)->nullable()->comment('Name of city.');
            $table->string('ship_to_state', 40)->nullable()->comment('State or province.');
            $table->string('ship_to_zip', 20)->nullable()->comment('U.S. ZIP code or other country-specific postal code.');
            $table->string('ship_to_country', 2)->nullable()->comment('Country code.');
            $table->string('ship_to_phone_num', 20)->nullable()->comment('Phone number.');
            $table->longText('transaction_log')->nullable();
            $table->dateTime('refunded_at')->nullable();
            $table->decimal('refunded_amt', 19, 4)->nullable();
            $table->string('refunded_auth', 50)->nullable();
            $table->integer('refunded_by')->unsigned()->nullable();
        });

        Schema::create('tribute_types', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('is_enabled')->default(0);
            $table->string('label', 50)->nullable();
            $table->integer('sequence')->unsigned()->nullable();
            $table->string('email_subject', 150)->nullable();
            $table->string('email_cc', 500)->nullable();
            $table->string('email_bcc', 500)->nullable();
            $table->mediumText('email_template')->nullable();
            $table->mediumText('letter_template')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->integer('deleted_by')->unsigned()->nullable();
            $table->string('dp_id', 10)->nullable();
        });

        Schema::create('tributes', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('order_item_id')->nullable()->index('order_item_id');
            $table->integer('tribute_type_id')->unsigned()->nullable()->index('tribute_type_id');
            $table->string('name', 50)->nullable();
            $table->decimal('amount', 19, 4)->default(0.0000);
            $table->string('message', 5000)->nullable();
            $table->string('notify', 25)->nullable();
            $table->string('notify_name', 150)->nullable();
            $table->string('notify_email', 150)->nullable();
            $table->string('notify_address', 250)->nullable();
            $table->string('notify_city', 100)->nullable();
            $table->string('notify_state', 4)->nullable();
            $table->string('notify_zip', 15)->nullable();
            $table->string('notify_country', 2)->nullable();
            $table->date('notify_at')->nullable();
            $table->date('notified_at')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->integer('deleted_by')->unsigned()->nullable();
        });

        Schema::create('user_logins', function (Blueprint $table) {
            $table->integer('id', true);
            $table->dateTime('login_at')->nullable();
            $table->integer('user_id')->index('user_id');
            $table->string('user_agent', 500);
            $table->string('ip', 20);
        });

        Schema::create('user', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('firstname', 45)->nullable();
            $table->string('lastname', 45)->nullable();
            $table->string('email', 45)->nullable();
            $table->string('password', 45)->nullable();
            $table->string('hashed_password', 64)->nullable();
            $table->string('primaryphonenumber', 45)->nullable();
            $table->string('alternatephonenumber', 45)->nullable();
            $table->string('isadminuser', 45)->default('0');
            $table->dateTime('createddatetime')->nullable();
            $table->dateTime('modifieddatetime')->nullable();
            $table->string('api_token', 60)->nullable();
            $table->string('credential', 100)->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->integer('deleted_by')->nullable()->index('deleted_by');
            $table->string('permissions_json', 2000)->nullable();
            $table->integer('created_by')->unsigned()->nullable();
            $table->integer('updated_by')->unsigned()->nullable();
            $table->custom('login_count', 'int(11)')->unsigned()->default(0);
            $table->dateTime('last_login_at')->nullable();
            $table->boolean('is_account_admin')->default(0);
            $table->boolean('ds_corporate_optin')->default(0);
            $table->boolean('notify_fundraising_page_abuse')->default(0);
            $table->boolean('notify_fundraising_page_activated')->default(0);
            $table->boolean('notify_fundraising_page_closed')->default(0);
        });

        Schema::create('userpermission', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('userid')->index('userid');
            $table->boolean('caneditpages')->unsigned()->nullable()->default(0);
            $table->boolean('canmanagepages')->unsigned()->nullable()->default(0);
            $table->boolean('caneditfeeds')->unsigned()->nullable()->default(0);
            $table->boolean('canmanagefeeds')->unsigned()->nullable()->default(0);
            $table->boolean('canmanagetemplates')->unsigned()->nullable()->default(0);
            $table->boolean('canmanageadmin')->unsigned()->nullable()->default(0);
            $table->boolean('canaccessstats')->unsigned()->nullable()->default(0);
            $table->boolean('canaccessemail')->unsigned()->nullable()->default(0);
            $table->boolean('canmanageecommerce')->unsigned()->nullable()->default(0);
            $table->boolean('can_check_in')->unsigned()->default(0);
        });

        Schema::create('variant_variant', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('variant_id')->index('variant_id');
            $table->integer('linked_variant_id')->index('linked_variant_id');
            $table->decimal('price', 19, 4);
            $table->custom('qty', 'int(11)')->unsigned()->default(1);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('account_types');
        Schema::drop('aliases');
        Schema::drop('assets');
        Schema::drop('audits');
        Schema::drop('autologin_tokens');
        Schema::drop('configs');
        Schema::drop('emails');
        Schema::drop('expense');
        Schema::drop('failed_jobs');
        Schema::drop('files');
        Schema::drop('fundraising_page_members');
        Schema::drop('fundraising_page_reports');
        Schema::drop('fundraising_pages');
        Schema::drop('group_account');
        Schema::drop('hook_deliveries');
        Schema::drop('hooks');
        Schema::drop('images');
        Schema::drop('imports');
        Schema::drop('jobs');
        Schema::drop('kiosk_sessions');
        Schema::drop('kiosks');
        Schema::drop('layout');
        Schema::drop('media');
        Schema::drop('mediables');
        Schema::drop('member');
        Schema::drop('member_login');
        Schema::drop('membership');
        Schema::drop('membership_access');
        Schema::drop('membership_promocodes');
        Schema::drop('metadata');
        Schema::drop('node');
        Schema::drop('nodecontent');
        Schema::drop('order_promocodes');
        Schema::drop('password_resets');
        Schema::drop('payment_methods');
        Schema::drop('payment_option');
        Schema::drop('payment_option_group');
        Schema::drop('payments');
        Schema::drop('payments_pivot');
        Schema::drop('pledgables');
        Schema::drop('pledges');
        Schema::drop('popup_logs');
        Schema::drop('popups');
        Schema::drop('post');
        Schema::drop('posttype');
        Schema::drop('product');
        Schema::drop('productaudioclip');
        Schema::drop('productcategory');
        Schema::drop('productcategorylink');
        Schema::drop('productfields');
        Schema::drop('productinventory');
        Schema::drop('productinventoryfiles');
        Schema::drop('productorder');
        Schema::drop('productorderitem');
        Schema::drop('productorderitemfield');
        Schema::drop('productorderitemfiles');
        Schema::drop('productorderitemtax');
        Schema::drop('productpromocode');
        Schema::drop('productpromocodecategory');
        Schema::drop('productpromocodeproduct');
        Schema::drop('producttax');
        Schema::drop('producttaxproduct');
        Schema::drop('producttaxregion');
        Schema::drop('recurring_payment_profiles');
        Schema::drop('refunds');
        Schema::drop('region');
        Schema::drop('reimbursement');
        Schema::drop('segment_items');
        Schema::drop('segments');
        Schema::drop('settings');
        Schema::drop('shipping_method');
        Schema::drop('shipping_tier');
        Schema::drop('shipping_value');
        Schema::drop('sponsors');
        Schema::drop('sponsorship');
        Schema::drop('sponsorship_payment_option_groups');
        Schema::drop('sponsorship_segments');
        Schema::drop('sql_migrations');
        Schema::drop('taggables');
        Schema::drop('tags');
        Schema::drop('tax_receipts');
        Schema::drop('templates');
        Schema::drop('ticket_check_in');
        Schema::drop('timelines');
        Schema::drop('transaction_fees');
        Schema::drop('transactions');
        Schema::drop('tribute_types');
        Schema::drop('tributes');
        Schema::drop('user');
        Schema::drop('user_logins');
        Schema::drop('userpermission');
        Schema::drop('variant_variant');
    }
}
