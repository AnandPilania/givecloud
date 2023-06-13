<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReferredByToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('productorder', function (Blueprint $table) {
            $table->integer('referred_by')->nullable()->index('ix_productorder_referred_by');
            $table->foreign('referred_by', 'productorder_referred_by_ibfk_1')->references('id')->on('member')->onUpdate('RESTRICT')->onDelete('RESTRICT');
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
            $table->dropForeign('productorder_referred_by_ibfk_1');
            $table->dropColumn(['referred_by']);
        });
    }
}
