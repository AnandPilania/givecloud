<?php

use Ds\Models\Transaction;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Query\JoinClause;

class FixTransactionLedgerEntryAmounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('ledger_entries as l')
            ->join('transactions as t', function (JoinClause $join) {
                $join->on('t.id', 'l.ledgerable_id');
                $join->where('l.ledgerable_type', Transaction::class);
            })->where('l.amount', '!=', DB::raw('t.amt'))
            ->update([
                'amount' => DB::raw('t.amt'),
                'discount' => 0,
                'original_amount' => DB::raw('t.amt'),
            ]);
    }
}
