<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTimespaceFkToGroupAccountTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('group_account', function (Blueprint $table) {
            $table->foreignId('group_account_timespan_id')
                ->nullable()
                ->after('account_id')
                ->constrained('group_account_timespan')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('group_account', function (Blueprint $table) {
            $table->dropConstrainedForeignId('group_account_timespan_id');
        });
    }
}
