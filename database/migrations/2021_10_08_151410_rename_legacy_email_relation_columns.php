<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameLegacyEmailRelationColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('emails', function (Blueprint $table) {
            $table->renameColumn('parent_type', 'parent_type_deprecated');
            $table->renameColumn('parent_id', 'parent_id_deprecated');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('emails', function (Blueprint $table) {
            $table->renameColumn('parent_type_deprecated', 'parent_type');
            $table->renameColumn('parent_id_deprecated', 'parent_id');
        });
    }
}
