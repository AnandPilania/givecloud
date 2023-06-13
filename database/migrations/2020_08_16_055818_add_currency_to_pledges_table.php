<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCurrencyToPledgesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pledges', function (Blueprint $table) {
            $table->char('currency_code', 3)->nullable()->after('total_amount');
            $table->char('functional_currency_code', 3)->nullable()->after('currency_code');
            $table->decimal('functional_exchange_rate', 23, 10)->default(1)->after('functional_currency_code');
            $table->decimal('functional_total_amount', 12, 2)->default(0)->after('functional_exchange_rate');
            $table->decimal('functional_funded_amount', 12, 2)->default(0)->after('functional_total_amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pledges', function (Blueprint $table) {
            $table->dropColumn([
                'currency_code',
                'functional_currency_code',
                'functional_exchange_rate',
                'functional_total_amount',
                'functional_funded_amount',
            ]);
        });
    }
}
