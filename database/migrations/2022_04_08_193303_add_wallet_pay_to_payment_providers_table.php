<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddWalletPayToPaymentProvidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_providers', function (Blueprint $table) {
            $table->boolean('is_wallet_pay_allowed')->default(0)->after('is_ach_allowed');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_providers', function (Blueprint $table) {
            $table->dropColumn('is_wallet_pay_allowed');
        });
    }
}
