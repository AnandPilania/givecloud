<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateUserMorphmaps extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('audits')
            ->where('user_type', 'Ds\\Models\\User')
            ->update(['user_type' => 'user']);

        DB::table('metadata')
            ->where('metadatable_type', 'Ds\\Models\\User')
            ->update(['metadatable_type' => 'user']);
    }
}
