<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddApplicationFeeAmountToPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->boolean('application_fee_billing')->default(false)->after('ip_country');
            $table->decimal('application_fee_amount', 19, 4)->nullable()->after('application_fee_billing');
            $table->decimal('stripe_fee_amount', 19, 4)->nullable()->after('platform_fee_type');
            $table->decimal('stripe_fee_exchange_rate', 23, 10)->nullable()->after('stripe_fee_amount');
            $table->char('stripe_fee_currency_code', 3)->nullable()->after('stripe_fee_exchange_rate');
        });

        Schema::table('refunds', function (Blueprint $table) {
            $table->decimal('application_fee_amount', 19, 4)->nullable()->after('failure_reason');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'application_fee_billing',
                'application_fee_amount',
                'stripe_fee_amount',
                'stripe_fee_exchange_rate',
                'stripe_fee_currency_code',
            ]);
        });

        Schema::table('refunds', function (Blueprint $table) {
            $table->dropColumn('application_fee_amount');
        });
    }
}
