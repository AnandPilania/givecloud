<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPlatformFeeTypeToRecurringPaymentProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('recurring_payment_profiles', function (Blueprint $table) {
            $table->string('platform_fee_type', 32)->nullable()->after('last_payment_amt');
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->string('platform_fee_type', 32)->nullable()->after('ip_country')->index();
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
            $table->dropColumn(['platform_fee_type']);
        });

        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['platform_fee_type']);
            $table->dropColumn('platform_fee_type');
        });
    }
}
