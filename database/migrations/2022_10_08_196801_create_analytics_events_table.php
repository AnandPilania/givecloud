<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnalyticsEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('analytics_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('analytics_visit_id')->constrained();
            $table->morphs('eventable');
            $table->string('event_name', 64);
            $table->string('event_category', 64);
            $table->string('event_value')->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_content')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->timestamps();

            $table->index(['eventable_type', 'eventable_id', 'event_name'], 'eventable_event_name_index');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('analytics_events');
    }
}
