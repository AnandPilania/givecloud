<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class P2pUpdatesToFundraisingPagesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('fundraising_pages', function (Blueprint $table) {
            $table->enum('type', ['standalone', 'website'])->default('website')->after('member_organizer_id');
            $table->string('avatar_name', 64)->nullable()->after('category');
            $table->string('team_join_code', 12)->collation('utf8_bin')->nullable()->after('team_photo_id');
            $table->foreignId('team_fundraising_page_id')->nullable()->after('team_join_code')->constrained('fundraising_pages');
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
            $table->dropColumn(['type', 'avatar_name', 'team_join_code']);
            $table->dropConstrainedForeignId('team_fundraising_page_id');
        });
    }
}
