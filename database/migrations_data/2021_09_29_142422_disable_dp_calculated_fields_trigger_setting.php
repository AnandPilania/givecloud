<?php

use Illuminate\Database\Migrations\Migration;

class DisableDpCalculatedFieldsTriggerSetting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        sys_set('dp_trigger_calculated_fields', 0);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
