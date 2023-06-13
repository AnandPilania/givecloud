<?php

use Illuminate\Database\Migrations\Migration;

class DisableFundraisingCustomDescriptions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // This is so that existing sites with fundraisers will
        // retain the ability to have custom descriptions, while all new
        // sites with default to not allow them.
        DB::insert('
            INSERT INTO settings (theme_id, name, value)
            SELECT id, ?, (CASE WHEN (SELECT COUNT(*) FROM fundraising_pages) > 0 THEN 1 ELSE 0 END)
            FROM themes
            WHERE handle = ?
        ', ['p2p_allow_custom_description', 'global']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::delete(
            'DELETE FROM settings
            WHERE name = ?
            AND theme_id = (SELECT id FROM themes WHERE handle = ?)',
            ['p2p_allow_custom_description', 'global']
        );
    }
}
