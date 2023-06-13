<?php

use Illuminate\Database\Migrations\Migration;

class MigrateSponsorshipSetting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $row = DB::table('settings')->where('name', 'sponsorship_show_age')->first();

        if ($row) {
            DB::table('settings')->insert([
                'theme_id' => $row->theme_id,
                'name' => 'sponsorship_show_birthday',
                'value' => $row->value,
            ]);
        }
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
