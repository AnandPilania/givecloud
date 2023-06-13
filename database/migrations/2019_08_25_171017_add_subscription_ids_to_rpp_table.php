<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSubscriptionIdsToRppTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('recurring_payment_profiles', function (Blueprint $table) {
            $table->string('paypal_subscription_id', 40)->nullable()->after('member_id');
            $table->string('stripe_subscription_id', 40)->nullable()->after('paypal_subscription_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('recurring_payment_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'paypal_subscription_id',
                'stripe_subscription_id',
            ]);
        });
    }
}
