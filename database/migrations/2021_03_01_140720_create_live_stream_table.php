<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLiveStreamTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('virtual_event_live_streams', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('virtual_event_id')->unsigned();
            $table->string('stream_id');
            $table->string('stream_key');
            $table->string('status');
            $table->string('streaming_video_id')->nullable();
            $table->string('playback_video_id')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('virtual_event_id')->references('id')->on('virtual_events');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('virtual_event_live_streams');
    }
}
