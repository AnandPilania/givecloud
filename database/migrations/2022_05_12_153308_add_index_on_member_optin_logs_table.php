<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexOnMemberOptinLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('member_optin_logs', function (Blueprint $table) {
            $table->foreign('member_id')->references('id')->on('member')->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('member_optin_logs', function (Blueprint $table) {
            $table->dropForeign(['member_id']);
        });
    }
}
