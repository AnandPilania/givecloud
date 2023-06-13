<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class BackfillCurrecyDataForPledges extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('pledges')->update([
            'currency_code' => (string) currency(),
            'functional_currency_code' => (string) currency(),
            'functional_total_amount' => DB::raw('total_amount'),
            'functional_funded_amount' => DB::raw('funded_amount'),
        ]);
    }
}
