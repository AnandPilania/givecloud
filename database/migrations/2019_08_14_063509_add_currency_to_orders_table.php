<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCurrencyToOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('productorder', function (Blueprint $table) {
            $table->char('currency_code', 3)->nullable()->after('total_savings');
            $table->char('presentation_currency_code', 3)->nullable()->after('currency_code');
            $table->decimal('presentation_exchange_rate', 23, 10)->nullable()->after('presentation_currency_code');
            $table->char('functional_currency_code', 3)->nullable()->after('presentation_exchange_rate');
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
        Schema::table('productorder', function (Blueprint $table) {
            $table->dropColumn([
                'currency_code',
                'presentation_currency_code',
                'presentation_exchange_rate',
                'functional_currency_code',
                'functional_exchange_rate',
                'functional_total',
            ]);
        });
    }
}
