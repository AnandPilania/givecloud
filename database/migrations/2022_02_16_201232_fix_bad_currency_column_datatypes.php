<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FixBadCurrencyColumnDatatypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('product', function (Blueprint $table) {
            $table->decimal('goal_progress_offset', 19, 4)->nullable()->change();
        });

        Schema::table('fundraising_page_members', function (Blueprint $table) {
            $table->decimal('amount_raised', 19, 4)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('product', function (Blueprint $table) {
            $table->decimal('goal_progress_offset', 10, 4)->nullable()->change();
        });

        Schema::table('fundraising_page_members', function (Blueprint $table) {
            $table->decimal('amount_raised', 10, 4)->nullable()->change();
        });
    }
}
