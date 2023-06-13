<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStripeIntentsToTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->string('stripe_setup_intent', 64)->collation('utf8_bin')->nullable()->after('stripe_customer_id');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('stripe_payment_intent', 64)->collation('utf8_bin')->nullable()->after('ip_country');
        });

        Schema::table('productorder', function (Blueprint $table) {
            $table->string('single_use_token', 64)->collation('utf8_bin')->nullable()->after('send_confirmation_emails');
            $table->string('stripe_payment_intent', 64)->collation('utf8_bin')->nullable()->after('single_use_token');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropColumn(['stripe_setup_intent']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['stripe_payment_intent']);
        });

        Schema::table('productorder', function (Blueprint $table) {
            $table->dropColumn([
                'single_use_token',
                'stripe_payment_intent',
            ]);
        });
    }
}
