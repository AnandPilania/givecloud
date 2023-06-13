<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContributionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contributions', function (Blueprint $table) {
            $table->id();
            $table->dateTime('contribution_date')->index();
            $table->decimal('total', 19, 4)->unsigned()->default(0);
            $table->decimal('total_refunded', 19, 4)->unsigned()->default(0);
            $table->char('currency_code', 3)->nullable();
            $table->char('functional_currency_code', 3)->nullable();
            $table->decimal('functional_exchange_rate', 23, 10)->default(1);
            $table->decimal('functional_total', 12, 2)->default(0);
            $table->boolean('is_pos')->default(0);
            $table->boolean('is_test')->default(0);
            $table->boolean('is_spam')->default(0);
            $table->boolean('is_refunded')->default(0);
            $table->boolean('is_fulfilled')->default(0);
            $table->foreignId('supporter_id')->nullable()->constrained('member');
            $table->char('billing_country', 2)->nullable();
            $table->string('source', 50)->nullable();
            $table->unsignedInteger('downloadable_items')->default(0);
            $table->unsignedInteger('fundraising_items')->default(0);
            $table->unsignedInteger('recurring_items')->default(0);
            $table->unsignedInteger('membership_items')->default(0);
            $table->unsignedInteger('shippable_items')->default(0);
            $table->unsignedInteger('sponsorship_items')->default(0);
            $table->enum('initiated_by', ['customer', 'merchant'])->nullable();
            $table->boolean('is_recurring')->default(0);
            $table->foreignId('payment_id')->nullable()->constrained('payments');
            $table->enum('payment_type', ['card', 'bank', 'paypal', 'cheque', 'cash', 'unknown'])->nullable();
            $table->enum('payment_status', ['succeeded', 'pending', 'failed'])->nullable();
            $table->string('payment_reference_number', 40)->nullable();
            $table->string('payment_gateway', 40)->nullable();
            $table->enum('payment_card_brand', ['American Express', 'Carte Blanche', 'China UnionPay', 'Diners Club', 'Discover', 'Elo', 'JCB', 'Laser', 'Maestro', 'MasterCard', 'Solo', 'Switch', 'UnionPay', 'Visa', 'Unknown'])->nullable();
            $table->char('payment_card_last4', 4)->nullable();
            $table->enum('payment_card_cvc_check', ['pass', 'fail', 'unavailable', 'unchecked'])->nullable();
            $table->enum('payment_card_address_line1_check', ['pass', 'fail', 'unavailable', 'unchecked'])->nullable();
            $table->enum('payment_card_address_zip_check', ['pass', 'fail', 'unavailable', 'unchecked'])->nullable();
            $table->string('payment_card_wallet', 12)->nullable();
            $table->boolean('ip_country_matches')->default(0);
            $table->boolean('is_dpo_synced')->default(0);
            $table->boolean('dpo_auto_sync')->default(0);
            $table->string('referral_source', 45)->nullable();
            $table->string('tracking_source', 50)->nullable();
            $table->string('tracking_medium', 50)->nullable();
            $table->string('tracking_campaign', 50)->nullable();
            $table->string('tracking_term', 50)->nullable();
            $table->string('tracking_content', 50)->nullable();
            $table->longText('searchable_text')->nullable();
            $table->dateTime('created_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('user');
            $table->dateTime('updated_at')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('user');
            $table->dateTime('deleted_at')->nullable();
            $table->foreignId('deleted_by')->nullable()->constrained('user');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('contributions');
    }
}
