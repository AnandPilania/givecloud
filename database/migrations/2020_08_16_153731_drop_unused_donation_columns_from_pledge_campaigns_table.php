<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropUnusedDonationColumnsFromPledgeCampaignsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pledge_campaigns', function (Blueprint $table) {
            $table->dropColumn([
                'first_donation_amount',
                'last_donation_amount',
            ]);
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
            $table->decimal('last_donation_amount', 12, 2)->nullable()->after('last_donation_date');
            $table->decimal('first_donation_amount', 12, 2)->nullable()->after('first_donation_date');
        });
    }
}
