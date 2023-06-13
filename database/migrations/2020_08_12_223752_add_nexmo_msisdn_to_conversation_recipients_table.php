<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNexmoMsisdnToConversationRecipientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('conversation_recipients', function (Blueprint $table) {
            $table->string('nexmo_msisdn', 34)->nullable()->after('resource_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('conversation_recipients', function (Blueprint $table) {
            $table->dropColumn('nexmo_msisdn');
        });
    }
}
