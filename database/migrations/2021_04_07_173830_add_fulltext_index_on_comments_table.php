<?php

use Illuminate\Database\Migrations\Migration;

class AddFulltextIndexOnCommentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement('ALTER TABLE comments ADD FULLTEXT search(body)');
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE comments DROP INDEX search');
    }
}
