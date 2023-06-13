<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFunctionalCurrencyToPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->char('functional_currency_code', 3)->nullable()->after('refunded');
            $table->decimal('functional_exchange_rate', 23, 10)->default(1)->after('functional_currency_code');
            $table->decimal('functional_total', 19, 4)->default(0)->after('functional_exchange_rate');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn([
                'functional_currency_code',
                'functional_exchange_rate',
                'functional_total',
            ]);
        });
    }
}
