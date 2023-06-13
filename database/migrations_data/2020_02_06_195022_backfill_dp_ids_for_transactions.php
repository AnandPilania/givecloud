<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class BackfillDpIdsForTransactions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('transactions')
            ->whereNotNull('dpo_gift_id')
            ->whereNull('alt_transaction_id')
            ->update([
                'alt_transaction_id' => DB::raw('dpo_gift_id'),
            ]);
    }
}
