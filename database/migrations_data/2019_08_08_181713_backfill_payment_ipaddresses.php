<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class BackfillPaymentIpaddresses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('payments as p')
            ->join('payments_pivot as pp', 'p.id', '=', 'pp.payment_id')
            ->join('productorder as o', 'pp.order_id', '=', 'o.id')
            ->update([
                'p.ip_address' => DB::raw('o.client_ip'),
                'p.ip_country' => DB::raw('o.ip_country'),
            ]);
    }
}
