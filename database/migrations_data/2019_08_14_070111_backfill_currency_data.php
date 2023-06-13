<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class BackfillCurrencyData extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $currency = currency();

        DB::table('productorder')->update([
            'currency_code'            => $currency->code,
            'functional_currency_code' => $currency->code,
            'functional_total'         => DB::raw('totalamount'),
        ]);

        DB::table('fundraising_pages')->update([
            'currency_code'            => $currency->code,
            'functional_currency_code' => $currency->code,
            'functional_goal_amount'   => DB::raw('goal_amount'),
            'functional_amount_raised' => DB::raw('amount_raised'),
        ]);

        // currency was never actually properly set on RPPs (subsequently cascading
        // down to transactions, payments and refunds as well). it just automatically
        // being set using the column default for the table. which means non-USD site will
        // incorrect currency code vaules on the RPPs/transactions/payments/refunds

        DB::table('recurring_payment_profiles')->update([
            'currency_code' => $currency->code,
        ]);

        DB::table('transactions')->update([
            'currency_code'            => $currency->code,
            'functional_currency_code' => $currency->code,
            'functional_total'         => DB::raw('amt'),
        ]);

        DB::table('payments')->update([
            'currency'                 => $currency->code,
            'functional_currency_code' => $currency->code,
            'functional_total'         => DB::raw('amount'),
        ]);

        DB::table('refunds')->update([
            'currency' => $currency->code,
        ]);
    }
}
