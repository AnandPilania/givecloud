<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddGuidelineAcceptanceDateToFundraisingPages extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fundraising_pages', function (Blueprint $table) {
            $table->dateTime('guidelines_accepted_at')->nullable()->after('activated_date');
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
            $table->dropColumn('guidelines_accepted_at');
        });
    }
}
