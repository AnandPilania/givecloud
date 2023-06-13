<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddHonorRollFlagForVirtualEvents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('virtual_events', function (Blueprint $table) {
            $table->boolean('is_honor_roll_enabled')->default(1)->after('celebration_threshold');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('virtual_events', function (Blueprint $table) {
            $table->dropColumn('is_honor_roll_enabled');
        });
    }
}
