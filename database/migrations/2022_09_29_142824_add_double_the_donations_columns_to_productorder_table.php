<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDoubleTheDonationsColumnsToProductorderTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('productorder', function (Blueprint $table) {
            $table->string('doublethedonation_company_id', 50)->nullable()->after('dp_sync_order');
            $table->string('doublethedonation_company_name')->nullable()->after('doublethedonation_company_id');
            $table->string('doublethedonation_status', 8)->nullable()->after('doublethedonation_company_name');
            $table->string('doublethedonation_entered_text')->nullable()->after('doublethedonation_status');
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
            $table->dropColumn('doublethedonation_company_id');
            $table->dropColumn('doublethedonation_company_name');
            $table->dropColumn('doublethedonation_status');
            $table->dropColumn('doublethedonation_entered_text');
        });
    }
}
