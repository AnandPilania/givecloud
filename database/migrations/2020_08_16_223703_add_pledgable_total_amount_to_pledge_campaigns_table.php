<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPledgableTotalAmountToPledgeCampaignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pledge_campaigns', function (Blueprint $table) {
            $table->decimal('pledgable_total_amount', 12, 2)->default(0)->after('first_donation_date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pledge_campaigns', function (Blueprint $table) {
            $table->dropColumn('pledgable_total_amount');
        });
    }
}
