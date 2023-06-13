<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCurrencyToFundraisingPagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fundraising_pages', function (Blueprint $table) {
            $table->char('currency_code', 3)->nullable()->after('donation_count_offset');
            $table->char('functional_currency_code', 3)->nullable()->after('currency_code');
            $table->decimal('functional_goal_amount', 19, 4)->default(0)->after('functional_currency_code');
            $table->decimal('functional_amount_raised', 19, 4)->default(0)->after('functional_goal_amount');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fundraising_pages', function (Blueprint $table) {
            $table->dropColumn([
                'currency_code',
                'functional_currency_code',
                'functional_goal_amount',
                'functional_amount_raised',
            ]);
        });
    }
}
