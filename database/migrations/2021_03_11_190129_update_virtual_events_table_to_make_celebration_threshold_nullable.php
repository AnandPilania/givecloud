<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateVirtualEventsTableToMakeCelebrationThresholdNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('virtual_events', function (Blueprint $table) {
            $table->integer('celebration_threshold')->nullable()->change();
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
            $table->integer('celebration_threshold')->nullable(false)->change();
        });
    }
}
