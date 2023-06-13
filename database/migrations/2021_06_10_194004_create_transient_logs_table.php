<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTransientLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('transient_logs', function (Blueprint $table) {
            $table->id();
            $table->string('origin', 12)->index();
            $table->string('level', 12)->index();
            $table->char('request_id', 36);
            $table->string('ip_address', 40)->nullable();
            $table->string('source')->index();
            $table->longText('message');
            $table->longText('context')->nullable();
            $table->foreignId('user_id')->nullable()->constrained('user');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('transient_logs');
    }
}
