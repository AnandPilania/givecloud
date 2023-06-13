<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDccTypeColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('productorder', function (Blueprint $table) {
            $table->string('dcc_type', 32)->nullable()->after('dcc_rate');
        });

        Schema::table('recurring_payment_profiles', function (Blueprint $table) {
            $table->string('dcc_type', 32)->nullable()->after('dcc_rate');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->string('dcc_type', 32)->nullable()->after('shipping_amt');
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
            $table->dropColumn(['dcc_type']);
        });

        Schema::table('recurring_payment_profiles', function (Blueprint $table) {
            $table->dropColumn(['dcc_type']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['dcc_type']);
        });
    }
}
