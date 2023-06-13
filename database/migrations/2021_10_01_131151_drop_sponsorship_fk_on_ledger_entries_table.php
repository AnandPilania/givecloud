<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropSponsorshipFkOnLedgerEntriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ledger_entries', function (Blueprint $table) {
            $table->dropForeign(['sponsorship_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ledger_entries', function (Blueprint $table) {
            $table->foreign('sponsorship_id')->references('id')->on('sponsorship');
        });
    }
}
