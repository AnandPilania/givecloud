<?php

use Ds\Models\HookEvent;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ChangeAccountPrefixesToSupporter extends Migration
{
    private $hookEventsTableName = '';

    public function __construct()
    {
        $this->hookEventsTableName = (new HookEvent)->getTable();
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table($this->hookEventsTableName)
            ->where('name', 'account_created')
            ->update(['name' => 'supporter_created']);

        DB::table($this->hookEventsTableName)
            ->where('name', 'account_updated')
            ->update(['name' => 'supporter_updated']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table($this->hookEventsTableName)
            ->where('name', 'supporter_created')
            ->update(['name' => 'account_created']);

        DB::table($this->hookEventsTableName)
            ->where('name', 'supporter_updated')
            ->update(['name' => 'account_updated']);
    }
}
