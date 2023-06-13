<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateVirtualEventsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('virtual_events', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->date('start_date');
            $table->integer('campaign_id')->unsigned();
            $table->string('logo')->nullable();
            $table->string('background_image')->nullable();
            $table->string('video_source')->nullable();
            $table->string('video_id')->nullable();
            $table->boolean('is_chat_enabled')->default(1);
            $table->string('chat_id')->nullable();
            $table->boolean('is_amount_tally_enabled')->default(1);
            $table->boolean('is_celebration_enabled')->default(1);
            $table->integer('celebration_threshold')->default(1000);
            $table->string('tab_one_label')->nullable();
            $table->integer('tab_one_product_id')->nullable();
            $table->string('tab_two_label')->nullable();
            $table->integer('tab_two_product_id')->nullable();
            $table->string('tab_three_label')->nullable();
            $table->integer('tab_three_product_id')->nullable();
            $table->boolean('is_demo_mode_enabled')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('campaign_id')->references('id')->on('pledge_campaigns');
            $table->foreign('tab_one_product_id')->references('id')->on('product');
            $table->foreign('tab_two_product_id')->references('id')->on('product');
            $table->foreign('tab_three_product_id')->references('id')->on('product');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('virtual_events');
    }
}
