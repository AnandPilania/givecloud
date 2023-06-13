<?php

use Illuminate\Database\Migrations\Migration;

class AdjustSupportLevels extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('configs')->where('config_key', 'support_chat')->where('config_value', 'low')->update([
            'config_value' => 'standard',
        ]);

        DB::table('configs')->where('config_key', 'support_chat')->where('config_value', 'asap')->update([
            'config_value' => 'high',
        ]);
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
