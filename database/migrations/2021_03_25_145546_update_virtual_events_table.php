<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateVirtualEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('virtual_events', function (Blueprint $table) {
            $table->string('prestream_message_line_1')->nullable()->after('is_demo_mode_enabled');
            $table->string('prestream_message_line_2')->nullable()->after('prestream_message_line_1');
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
            $table->dropColumn('prestream_message_line_1');
            $table->dropColumn('prestream_message_line_2');
        });
    }
}
