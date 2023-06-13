<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMonitoringIncidentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('monitoring_incidents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('incident_type', 64)->index();
            $table->dateTime('triggered_at');
            $table->dateTime('recovered_at')->nullable();
            $table->integer('recovered_by')->nullable();
            $table->string('action_taken', 64)->nullable();
            $table->dateTime('last_notified_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('monitoring_incidents');
    }
}
