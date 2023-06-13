<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOrderPromocodeFks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('order_promocodes', function (Blueprint $table) {
            $table->integer('order_id')->change();

            $table->foreign('order_id')->references('id')->on('productorder')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('order_promocodes', function (Blueprint $table) {
            $table->dropForeign('order_promocodes_order_id_foreign');
        });

        Schema::table('order_promocodes', function (Blueprint $table) {
            $table->unsignedInteger('order_id')->change();
        });
    }
}
