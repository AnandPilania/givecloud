<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToFeatureAffinityTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->foreign('media_id', 'categories_ibfk_1')->references('id')->on('media')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });

        Schema::table('conversations_pivot', function (Blueprint $table) {
            $table->foreign('conversation_id', 'conversations_pivot_ibfk_1')->references('id')->on('conversations')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('conversation_recipient_id', 'conversations_pivot_ibfk_2')->references('id')->on('conversation_recipients')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });

        Schema::table('resumable_conversations', function (Blueprint $table) {
            $table->foreign('conversation_id', 'resumable_conversations_ibfk_1')->references('id')->on('conversations')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('account_id', 'resumable_conversations_ibfk_2')->references('id')->on('member')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });

        Schema::table('stock_adjustments', function (Blueprint $table) {
            $table->foreign('variant_id', 'stock_adjustments_ibfk_1')->references('id')->on('productinventory')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('user_id', 'stock_adjustments_ibfk_2')->references('id')->on('user')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('payment_id', 'stock_adjustments_ibfk_3')->references('id')->on('payments')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('refund_id', 'stock_adjustments_ibfk_4')->references('id')->on('refunds')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropForeign('categories_ibfk_1');
        });

        Schema::table('conversations_pivot', function (Blueprint $table) {
            $table->dropForeign('conversations_pivot_ibfk_1');
            $table->dropForeign('conversations_pivot_ibfk_2');
        });

        Schema::table('resumable_conversations', function (Blueprint $table) {
            $table->dropForeign('resumable_conversations_ibfk_1');
            $table->dropForeign('resumable_conversations_ibfk_2');
        });

        Schema::table('stock_adjustments', function (Blueprint $table) {
            $table->dropForeign('stock_adjustments_ibfk_1');
            $table->dropForeign('stock_adjustments_ibfk_2');
            $table->dropForeign('stock_adjustments_ibfk_3');
            $table->dropForeign('stock_adjustments_ibfk_4');
        });
    }
}
