<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddReferredByToMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('member', function (Blueprint $table) {
            $table->integer('referred_by')->nullable()->index('ix_member_referred_by');
            $table->foreign('referred_by', 'member_referred_by_ibfk_1')->references('id')->on('member')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('member', function (Blueprint $table) {
            $table->dropForeign('member_referred_by_ibfk_1');
            $table->dropColumn(['referred_by']);
        });
    }
}
