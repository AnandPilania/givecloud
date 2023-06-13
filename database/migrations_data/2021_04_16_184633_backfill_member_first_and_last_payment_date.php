<?php

use Illuminate\Database\Migrations\Migration;

class BackfillMemberFirstAndLastPaymentDate extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('member')->update([
            'first_payment_at' => DB::raw('(SELECT min(created_at) FROM payments WHERE paid = 1 AND refunded = 0 AND source_account_id = member.id)'),
            'last_payment_at' => DB::raw('(SELECT max(created_at) FROM payments WHERE paid = 1 AND refunded = 0 AND source_account_id = member.id)'),
        ]);
    }
}
