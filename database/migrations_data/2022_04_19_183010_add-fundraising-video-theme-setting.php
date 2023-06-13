<?php

use Illuminate\Database\Migrations\Migration;

class AddFundraisingVideoThemeSetting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // This is so that existing sites with fundraisers will
        // retain the ability to upload images, while all new
        // sites with default to not allowing image uploads.
        DB::insert("
            INSERT INTO settings (theme_id, name, value)
            SELECT id, ?, (CASE WHEN (SELECT COUNT(*) FROM fundraising_pages WHERE video_url != '') > 0 THEN 1 ELSE 0 END)
            FROM themes
            WHERE handle = ?
        ", ['p2p_page_allow_video_link', 'global']);
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
            ['p2p_page_allow_video_link', 'global']
        );
    }
}
