<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMorphsToAutologinTokensTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('autologin_tokens', function (Blueprint $table) {
            $table->renameColumn('account_id', 'user_id');
            $table->string('user_type')->after('id')->default('account');
            $table->index(['user_type', 'user_id']);
            $table->dropForeign('autologin_tokens_ibfk_1');
            // $table->dropIndex('user_id');
        });

        Schema::table('autologin_tokens', function (Blueprint $table) {
            $table->string('user_type')->after('id')->default(null)->change();
            $table->unsignedInteger('user_id')->after('user_type')->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('autologin_tokens', function (Blueprint $table) {
            $table->integer('user_id')->after('user_type')->change();
        });

        Schema::table('autologin_tokens', function (Blueprint $table) {
            $table->dropIndex('autologin_tokens_user_type_user_id_index');
            $table->renameColumn('user_id', 'account_id');
            $table->dropColumn('user_type');
            $table->foreign('account_id', 'autologin_tokens_ibfk_1')->references('id')->on('member');
        });
    }
}
