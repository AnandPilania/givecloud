<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropPopupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('popup_logs');
        Schema::drop('popups');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('popup_logs', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('popup_id')->nullable()->index('ix_popup_logs_popup_id');
            $table->dateTime('logged_at')->nullable();
            $table->string('type', 15)->nullable()->index('ix_popup_logs_type');
            $table->string('ip_address', 45)->nullable();
        });

        Schema::create('popups', function (Blueprint $table) {
            $table->integer('id', true);
            $table->custom('is_deleted', 'tinyint(4)')->default(0)->index('ix_popup_is_deleted');
            $table->string('name', 125)->nullable();
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
            $table->text('html', 65535)->nullable()->comment('the popup content');
            $table->integer('timeout')->default(0)->comment('milliseconds before we show the popup');
            $table->integer('conversion_limit')->default(0)->comment('max conversions (ex: only capture 50 email addresses)');
            $table->text('email_response', 65535)->nullable()->comment('the text to be emailed');
            $table->integer('node_id')->nullable()->index('ix_popup_node_id');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('updated_at')->nullable();
            $table->integer('created_by')->nullable()->index('created_by');
            $table->integer('updated_by')->nullable()->index('updated_by');
            $table->custom('is_enabled', 'tinyint(4)')->default(0);
            $table->text('success_message', 65535)->nullable();
        });

        Schema::table('popup_logs', function (Blueprint $table) {
            $table->foreign('popup_id', 'popup_logs_ibfk_1')->references('id')->on('popups')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });

        Schema::table('popups', function (Blueprint $table) {
            $table->foreign('created_by', 'popups_ibfk_1')->references('id')->on('user')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('updated_by', 'popups_ibfk_2')->references('id')->on('user')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('node_id', 'popups_ibfk_3')->references('id')->on('node')->onUpdate('RESTRICT')->onDelete('RESTRICT');
        });
    }
}
