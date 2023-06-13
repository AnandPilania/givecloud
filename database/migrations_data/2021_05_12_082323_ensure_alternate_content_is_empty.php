<?php

use Ds\Models\Setting;
use Illuminate\Database\Migrations\Migration;

class EnsureAlternateContentIsEmpty extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Setting::query()
            ->where('name', 'pg_with_pay_panel_content')
            ->where('value', '<p><br></p>')
            ->update([
                'value' => null,
            ]);
    }
}
