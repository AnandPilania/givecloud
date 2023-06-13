<?php

use Illuminate\Database\Migrations\Migration;

class BackfillSegmentTypes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement(sprintf(
            'UPDATE segments SET type = (CASE %s END)',
                "WHEN is_text_only = 0 AND is_simple = 1 THEN 'multi-select' " .
                "WHEN is_text_only = 0 AND is_simple = 0 THEN 'advanced-multi-select' " .
                "ELSE 'text'"
        ));
    }
}
