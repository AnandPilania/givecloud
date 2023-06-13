<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropUnusedTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('expense');
        Schema::dropIfExists('images');

        Schema::table('nodecontent', function (Blueprint $table) {
            $table->dropForeign('nodecontent_ibfk_4');
            $table->dropColumn('layoutid');
        });

        Schema::dropIfExists('layout');
        Schema::dropIfExists('productaudioclip');
        Schema::dropIfExists('reimbursement');
        Schema::dropIfExists('templates');
        Schema::dropIfExists('userpermission');
    }
}
