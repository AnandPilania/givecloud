<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAvatarOnSocialIdentities extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('social_identities', function (Blueprint $table) {
            $table->dropUnique(['provider_id']);
            $table->unique([
                'provider_name', 'provider_id', 'authenticatable_id', 'authenticatable_type',
            ], 'provider_authenticatable_idx');
            $table->text('avatar')->nullable()->after('is_confirmed');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('social_identities', function (Blueprint $table) {
            $table->dropColumn('avatar');
            $table->dropUnique('provider_authenticatable_idx');
            $table->unique(['provider_id']);
        });
    }
}
