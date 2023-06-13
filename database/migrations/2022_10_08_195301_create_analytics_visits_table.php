<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAnalyticsVisitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('analytics_visits', function (Blueprint $table) {
            $table->id();
            $table->char('visitor_id', 36);
            $table->dateTime('visitor_localtime')->nullable();
            $table->boolean('visitor_returning')->default(false);
            $table->unsignedInteger('visitor_count_visits')->default(0);
            $table->unsignedInteger('visitor_days_since_last')->nullable();
            $table->unsignedInteger('visitor_days_since_contribution')->nullable();
            $table->unsignedInteger('visitor_days_since_first')->default(0);
            $table->unsignedInteger('visit_total_events')->default(0);
            $table->unsignedInteger('visit_total_time')->default(0);
            $table->boolean('visit_contribution_converted')->default(false);
            $table->string('location_ip', 40)->nullable();
            $table->string('location_city')->nullable();
            $table->char('location_state', 2)->nullable();
            $table->char('location_country', 2)->nullable();
            $table->decimal('location_latitude', 10, 8)->nullable();
            $table->decimal('location_longitude', 11, 8)->nullable();
            $table->char('location_language', 5)->nullable();
            $table->char('location_timezone')->nullable();
            $table->string('referrer_name')->nullable();
            $table->mediumText('referrer_url')->nullable();
            $table->string('config_type', 8)->nullable(); // desktop/mobile/tablet/bot
            $table->string('config_platform_name', 64)->nullable();
            $table->string('config_platform_version', 32)->nullable();
            $table->string('config_device_name', 64)->nullable();
            $table->string('config_device_brand', 64)->nullable();
            $table->string('config_browser_name', 64)->nullable();
            $table->string('config_browser_version', 32)->nullable();
            $table->string('config_bot_name', 64)->nullable();
            $table->mediumText('config_user_agent')->nullable();
            $table->string('utm_source')->nullable();
            $table->string('utm_medium')->nullable();
            $table->string('utm_content')->nullable();
            $table->string('utm_campaign')->nullable();
            $table->foreignId('member_id')->nullable();
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
        Schema::dropIfExists('analytics_visits');
    }
}
