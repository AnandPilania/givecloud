<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CorrectDpEnabledMetadataName extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('metadata')
            ->where('key', 'donation_forms_dp_enabled')
            ->update(['key' => 'dp_syncable']);
    }
}
