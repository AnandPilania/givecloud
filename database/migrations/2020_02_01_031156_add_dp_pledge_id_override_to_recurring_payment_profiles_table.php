<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDpPledgeIdOverrideToRecurringPaymentProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('recurring_payment_profiles', function (Blueprint $table) {
            $table->string('dp_pledge_id_override', 10)->nullable();
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
            $table->dropColumn('dp_pledge_id_override');
        });
    }
}
