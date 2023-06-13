<?php

use Illuminate\Database\Migrations\Migration;

class ChangeNodeContentFk extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE nodecontent DROP FOREIGN KEY nodecontent_ibfk_3');
        DB::statement('ALTER TABLE nodecontent ADD CONSTRAINT nodecontent_ibfk_3 FOREIGN KEY nodecontent_ibfk_3 (nodeid) REFERENCES node (id) ON DELETE CASCADE ON UPDATE RESTRICT');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE nodecontent DROP FOREIGN KEY nodecontent_ibfk_3');
        DB::statement('ALTER TABLE nodecontent ADD CONSTRAINT nodecontent_ibfk_3 FOREIGN KEY nodecontent_ibfk_3 (nodeid) REFERENCES node (id) ON DELETE RESTRICT ON UPDATE RESTRICT');
    }
}
