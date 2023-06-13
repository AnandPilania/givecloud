<?php

use Illuminate\Database\Migrations\Migration;

class UpdateDatesForOfflinePayments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('payments as p')
            ->join('payments_pivot as pp', 'pp.payment_id', '=', 'p.id')
            ->join('productorder as o', 'o.id', '=', 'pp.order_id')
            ->where('p.gateway_type', 'offline')
            ->whereRaw('DATE(p.created_at) != DATE(o.ordered_at)')
            ->whereNotNull('o.ordered_at')
            ->whereNotNull('p.captured_at')
            ->where('o.is_pos', 1)
            ->update([
                'p.created_at'  => DB::raw('o.ordered_at'),
                'p.captured_at' => DB::raw('o.ordered_at'),
            ]);
    }
}
