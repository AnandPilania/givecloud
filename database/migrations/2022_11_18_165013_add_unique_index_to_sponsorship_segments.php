<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddUniqueIndexToSponsorshipSegments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sponsorship_segments', function (Blueprint $table) {
            $table->unique(['sponsorship_id', 'segment_id']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sponsorship_segments', function (Blueprint $table) {
            $table->dropUnique(['sponsorship_id', 'segment_id']);
        });
    }
}
