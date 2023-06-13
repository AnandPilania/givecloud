<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLifetimeTotalsToMembersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('member', function (Blueprint $table) {
            $table->decimal('lifetime_donation_amount', 19, 4)->default(0);
            $table->integer('lifetime_donation_count')->default(0);
            $table->decimal('lifetime_purchase_amount', 19, 4)->default(0);
            $table->integer('lifetime_purchase_count')->default(0);
            $table->decimal('lifetime_fundraising_amount', 19, 4)->default(0);
            $table->integer('lifetime_fundraising_count')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('member', function (Blueprint $table) {
            $table->dropColumn('lifetime_donation_amount');
            $table->dropColumn('lifetime_donation_count');
            $table->dropColumn('lifetime_purchase_amount');
            $table->dropColumn('lifetime_purchase_count');
            $table->dropColumn('lifetime_fundraising_amount');
            $table->dropColumn('lifetime_fundraising_count');
        });
    }
}
