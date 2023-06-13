<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class MigrateToNewDpAutoSyncSetting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('metadata as m')
            ->join('product as p', function ($join) {
                $join->on('p.id', 'm.metadatable_id');
                $join->where('m.metadatable_type', 'product');
            })->where('p.type', 'donation_form')
            ->where('m.key', 'dp_syncable')
            ->update(['m.key' => 'donation_forms_dp_autosync_enabled']);
    }
}
