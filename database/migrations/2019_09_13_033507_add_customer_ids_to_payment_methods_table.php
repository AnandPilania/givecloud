<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCustomerIdsToPaymentMethodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->string('authorizenet_customer_id', 40)->nullable()->after('billing_phone');
            $table->string('paypal_payer_id', 40)->nullable()->after('authorizenet_customer_id');
            $table->string('paysafe_profile_id', 40)->nullable()->after('paypal_payer_id');
            $table->string('stripe_customer_id', 64)->nullable()->after('paysafe_profile_id');
            $table->string('vanco_customer_ref', 40)->nullable()->after('stripe_customer_id');
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
            $table->dropColumn([
                'authorizenet_customer_id',
                'paypal_payer_id',
                'paysafe_profile_id',
                'stripe_customer_id',
                'vanco_customer_ref',
            ]);
        });
    }
}
