<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUpgradeFlagToOrderItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('productorderitem', function (Blueprint $table) {
            $table->boolean('upgraded_to_recurring')->default(0)->after('recurring_ends_on');
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
            $table->dropColumn('upgraded_to_recurring');
        });
    }
}
