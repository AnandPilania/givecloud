<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('productorder', function (Blueprint $table) {
            $table->index('confirmationdatetime');
            $table->index('ordered_at');
            $table->index('started_at');
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
            $table->dropIndex('productorder_confirmationdatetime_index');
            $table->dropIndex('productorder_ordered_at_index');
            $table->dropIndex('productorder_started_at_index');
        });
    }
}
