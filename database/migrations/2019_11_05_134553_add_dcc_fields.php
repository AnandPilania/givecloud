<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDccFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('productorder', function (Blueprint $table) {
            $table->boolean('dcc_enabled_by_customer')->default(0)->after('tax_country');
            $table->decimal('dcc_per_order_amount', 19, 4)->default(0)->after('dcc_enabled_by_customer');
            $table->decimal('dcc_rate', 19, 4)->default(0)->after('dcc_per_order_amount');
            $table->decimal('dcc_total_amount', 19, 4)->default(0)->after('dcc_rate');
        });

        Schema::table('productorderitem', function (Blueprint $table) {
            $table->boolean('dcc_eligible')->default(0)->after('sponsorship_expired_reason')->description('Copies the value from the product table so we retain a record of whether the product had dcc enabled at the time of purchase');
            $table->decimal('dcc_amount', 19, 4)->default(0)->after('dcc_eligible');
            $table->decimal('dcc_recurring_amount', 19, 4)->default(0)->after('dcc_amount');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->decimal('dcc_amount', 19, 4)->default(0)->after('shipping_amt');
        });

        Schema::table('recurring_payment_profiles', function (Blueprint $table) {
            $table->boolean('dcc_enabled_by_customer')->default(0)->after('init_amt');
            $table->decimal('dcc_per_order_amount', 19, 4)->default(0)->after('dcc_enabled_by_customer');
            $table->decimal('dcc_rate', 19, 4)->default(0)->after('dcc_per_order_amount');
            $table->decimal('dcc_amount', 19, 4)->default(0)->after('dcc_rate');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('productorder', function (Blueprint $table) {
            $table->dropColumn('dcc_enabled_by_customer');
            $table->dropColumn('dcc_per_order_amount');
            $table->dropColumn('dcc_rate');
            $table->dropColumn('dcc_total_amount');
        });

        Schema::table('productorderitem', function (Blueprint $table) {
            $table->dropColumn('dcc_eligible');
            $table->dropColumn('dcc_amount');
            $table->dropColumn('dcc_recurring_amount');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('dcc_amount');
        });

        Schema::table('recurring_payment_profiles', function (Blueprint $table) {
            $table->dropColumn('dcc_enabled_by_customer');
            $table->dropColumn('dcc_per_order_amount');
            $table->dropColumn('dcc_rate');
            $table->dropColumn('dcc_amount');
        });
    }
}
