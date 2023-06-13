<?php

use Ds\Models\SocialIdentity;
use Ds\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeSocialIdentitiesTableMorphable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('social_identities', function (Blueprint $table) {
            $table->morphs('authenticatable', 'authenticatable_idx');
        });

        SocialIdentity::query()->update([
            'authenticatable_id' => DB::raw('user_id'),
            'authenticatable_type' => (new User)->getMorphClass(),
        ]);

        Schema::table('social_identities', function (Blueprint $table) {
            $table->dropConstrainedForeignId('user_id');
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
            $table->foreignId('user_id')->nullable()->constrained('user');
        });

        SocialIdentity::query()->update([
            'user_id' => DB::raw('authenticatable_id'),
        ]);

        Schema::table('social_identities', function (Blueprint $table) {
            $table->dropMorphs('authenticatable', 'authenticatable_idx');
        });
    }
}
