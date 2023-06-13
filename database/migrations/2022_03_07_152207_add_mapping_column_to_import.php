<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMappingColumnToImport extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('imports', function (Blueprint $table) {
            $table->json('field_mapping')->nullable()->after('file_path');
            $table->boolean('file_has_headers')->after('field_mapping')->default(true);
            $table->json('file_infos')->nullable()->after('file_has_headers');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('imports', function (Blueprint $table) {
            $table->dropColumn('field_mapping');
            $table->dropColumn('file_has_headers');
            $table->dropColumn('file_infos');
        });
    }
}
