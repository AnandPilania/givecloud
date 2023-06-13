<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddNotifyFlagsToUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user', function (Blueprint $table) {
            $table->boolean('notify_digest_daily')->default(0)->after('ds_corporate_optin');
            $table->boolean('notify_digest_weekly')->default(0)->after('notify_digest_daily');
            $table->boolean('notify_digest_monthly')->default(0)->after('notify_digest_weekly');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user', function (Blueprint $table) {
            $table->dropColumn([
                'notify_digest_daily',
                'notify_digest_weekly',
                'notify_digest_monthly',
            ]);
        });
    }
}
