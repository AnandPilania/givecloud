<?php

use Illuminate\Database\Migrations\Migration;

class TrimConfigEmailRecords extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ($from = sys_get('email_from_address')) {
            sys_set('email_from_address', trim($from));
        }

        if ($replyTo = sys_get('email_replyto_address')) {
            sys_set('email_replyto_address', trim($replyTo));
        }
    }
}
