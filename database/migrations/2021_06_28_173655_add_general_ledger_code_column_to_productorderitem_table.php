<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGeneralLedgerCodeColumnToProductorderitemTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('productorderitem', function (Blueprint $table) {
            $table->string('general_ledger_code', 200)->nullable()->after('dcc_recurring_amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('productorderitem', function (Blueprint $table) {
            $table->dropColumn('general_ledger_code');
        });
    }
}
