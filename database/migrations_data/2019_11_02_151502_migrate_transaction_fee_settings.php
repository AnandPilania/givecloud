<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class MigrateTransactionFeeSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        sys_set('dcc_enabled', (bool) volt_setting('pg_with_pay_cover_fees'));
        sys_set('dcc_cost_per_order', sys_get('ss_txn_cost'));
        sys_set('dcc_percentage', sys_get('ss_txn_rate'));

        DB::table('product')
            ->where('template_suffix', 'page-with-payment')
            ->update([
                'is_dcc_enabled' => '1',
            ]);
    }
}
