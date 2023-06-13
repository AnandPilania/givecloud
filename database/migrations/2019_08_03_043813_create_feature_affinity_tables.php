<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateFeatureAffinityTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->custom('id', 'int(11)', true)->unsigned();
            $table->custom('parent_id', 'int(11)')->unsigned()->nullable();
            $table->custom('assignable_id', 'int(11)')->unsigned()->nullable();
            $table->string('assignable_type', 64);
            $table->custom('sequence', 'int(11)')->unsigned()->nullable()->default(0);
            $table->boolean('enabled')->default(1);
            $table->string('name', 64);
            $table->string('handle', 150);
            $table->text('description', 65535)->nullable();
            $table->custom('media_id', 'int(11)')->unsigned()->nullable()->index('media_id');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->custom('created_by', 'int(11)')->unsigned()->nullable();
            $table->custom('updated_by', 'int(11)')->unsigned()->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->custom('deleted_by', 'int(11)')->unsigned()->nullable();
        });

        Schema::create('categorizables', function (Blueprint $table) {
            $table->custom('category_id', 'int(11)')->unsigned();
            $table->custom('categorizable_id', 'int(11)')->unsigned();
            $table->string('categorizable_type', 64);
            $table->primary(['category_id', 'categorizable_id', 'categorizable_type'], 'composite_primary_key');
        });

        Schema::create('conversation_recipients', function (Blueprint $table) {
            $table->increments('id');
            $table->string('identifier', 34)->comment('E.164 formatted phone number or a 5-6 digit short code');
            $table->enum('resource_type', ['phone_number', 'short_code']);
            $table->char('twilio_sid', 34)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        Schema::create('conversations_pivot', function (Blueprint $table) {
            $table->integer('conversation_id')->unsigned()->index('conversations_pivot_ibfk_1');
            $table->integer('conversation_recipient_id')->unsigned()->index('conversation_recipient_id');
            $table->primary(['conversation_id', 'conversation_recipient_id'], 'composite_primary_key');
        });

        Schema::create('conversations', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('enabled')->unsigned()->default(0);
            $table->string('command', 128);
            $table->string('conversation_type', 32);
            $table->string('tracking_source', 50)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        Schema::create('payment_providers', function (Blueprint $table) {
            $table->custom('id', 'int(11)', true)->unsigned();
            $table->boolean('enabled')->default(1);
            $table->string('display_name', 64);
            $table->string('provider', 32);
            $table->string('provider_type', 24);
            $table->text('credential1', 65535)->nullable();
            $table->text('credential2', 65535)->nullable();
            $table->text('credential3', 65535)->nullable();
            $table->text('credential4', 65535)->nullable();
            $table->text('seller_note', 65535)->nullable();
            $table->boolean('show_payment_method')->default(0);
            $table->boolean('require_cvv')->default(0);
            $table->boolean('card_verification')->default(0);
            $table->boolean('deny_if_prepaid')->default(0);
            $table->boolean('is_ach_allowed')->default(0);
            $table->integer('duplicate_window')->default(1200);
            $table->boolean('test_mode')->default(0);
            $table->string('cards', 32)->nullable();
            $table->longText('config')->nullable();
            $table->decimal('transaction_cost', 19, 4)->nullable();
            $table->float('transaction_rate', 10, 0)->nullable();
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->custom('created_by', 'int(11)')->unsigned()->nullable();
            $table->custom('updated_by', 'int(11)')->unsigned()->nullable();
            $table->dateTime('deleted_at')->nullable();
            $table->custom('deleted_by', 'int(11)')->unsigned()->nullable();
        });

        Schema::create('pledge_campaigns', function (Blueprint $table) {
            $table->custom('id', 'int(11)', true)->unsigned();
            $table->string('name', 64);
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('total_count')->unsigned()->default(0);
            $table->decimal('total_amount', 12)->default(0.00);
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

        Schema::create('resumable_conversations', function (Blueprint $table) {
            $table->increments('id');
            $table->string('driver');
            $table->string('sender', 34);
            $table->string('recipient', 34);
            $table->text('message', 65535);
            $table->integer('conversation_id')->unsigned()->index('conversation_id');
            $table->longText('parameters')->nullable();
            $table->integer('account_id')->nullable()->index('account_id');
            $table->enum('resume_on', ['account_verified', 'payment_method_added']);
            $table->dateTime('expires');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        Schema::create('stock_adjustments', function (Blueprint $table) {
            $table->integer('id', true);
            $table->enum('type', ['physical_count', 'adjustment']);
            $table->integer('variant_id')->index('variant_id');
            $table->enum('state', ['in_stock', 'sold']);
            $table->integer('quantity');
            $table->dateTime('occurred_at')->nullable();
            $table->string('note', 191)->nullable();
            $table->integer('user_id')->nullable()->index('user_id');
            $table->bigInteger('payment_id')->unsigned()->nullable()->index('payment_id');
            $table->bigInteger('refund_id')->unsigned()->nullable()->index('refund_id');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });

        Schema::create('themes', function (Blueprint $table) {
            $table->integer('id', true);
            $table->string('handle', 64);
            $table->string('title', 64);
            $table->string('description');
            $table->string('source', 32);
            $table->boolean('locked')->default(1);
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('categories');
        Schema::drop('categorizables');
        Schema::drop('conversation_recipients');
        Schema::drop('conversations_pivot');
        Schema::drop('conversations');
        Schema::drop('payment_providers');
        Schema::drop('pledge_campaigns');
        Schema::drop('resumable_conversations');
        Schema::drop('stock_adjustments');
        Schema::drop('themes');
    }
}
