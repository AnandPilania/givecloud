<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateMoneyTypesOnFundraisingPagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fundraising_pages', function (Blueprint $table) {
            $table->decimal('goal_amount', 19, 4)->nullable()->change();
            $table->decimal('amount_raised', 19, 4)->default(0)->change();
            $table->decimal('amount_raised_offset', 19, 4)->default(0)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fundraising_pages', function (Blueprint $table) {
            $table->decimal('goal_amount', 10, 4)->nullable()->change();
            $table->decimal('amount_raised', 10, 4)->default(0)->change();
            $table->decimal('amount_raised_offset', 10, 4)->default(0)->nullable()->change();
        });
    }
}
