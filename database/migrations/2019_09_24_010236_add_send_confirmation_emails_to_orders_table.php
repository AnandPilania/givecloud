<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSendConfirmationEmailsToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('productorder', function (Blueprint $table) {
            $table->boolean('send_confirmation_emails')->default(1)->after('email_opt_in');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('productorder', function (Blueprint $table) {
            $table->dropColumn('send_confirmation_emails');
        });
    }
}
