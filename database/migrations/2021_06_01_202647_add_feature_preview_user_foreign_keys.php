<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFeaturePreviewUserForeignKeys extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('feature_preview_user_states', function (Blueprint $table) {
            $table->foreignId('user_id')->change();
            $table->foreign('user_id')->references('id')->on('user');
        });

        Schema::table('feature_preview_user_state_activities', function (Blueprint $table) {
            $table->foreignId('user_id')->change();
            $table->foreign('user_id')->references('id')->on('user');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('feature_preview_user_states', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('feature_preview_user_state_activities', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
        });

        Schema::table('feature_preview_user_states', function (Blueprint $table) {
            $table->integer('user_id')->change();
        });

        Schema::table('feature_preview_user_state_activities', function (Blueprint $table) {
            $table->integer('user_id')->change();
        });
    }
}
