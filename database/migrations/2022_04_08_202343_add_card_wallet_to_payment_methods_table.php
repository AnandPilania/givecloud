<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCardWalletToPaymentMethodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->unsignedBigInteger('member_id')->nullable()->change();
            $table->string('cc_wallet', 12)->nullable()->after('cc_expiry');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::disableForeignKeyConstraints();

        Schema::table('payment_methods', function (Blueprint $table) {
            $table->unsignedBigInteger('member_id')->nullable(false)->change();
            $table->dropColumn('cc_wallet');
        });

        Schema::enableForeignKeyConstraints();
    }
}
