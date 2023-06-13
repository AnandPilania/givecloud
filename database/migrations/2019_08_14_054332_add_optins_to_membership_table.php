<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOptinsToMembershipTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('membership', function (Blueprint $table) {
            $table->boolean('double_optin_required')->default(0)->after('days_to_expire');
            $table->boolean('members_can_view_directory')->default(0)->after('days_to_expire');
            $table->boolean('members_can_manage_optout')->default(0)->after('days_to_expire');
            $table->boolean('members_can_manage_optin')->default(0)->after('days_to_expire');
            $table->string('public_description')->nullable()->after('days_to_expire');
            $table->string('public_name')->nullable()->after('days_to_expire');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('membership', function (Blueprint $table) {
            $table->dropColumn([
                'double_optin_required',
                'members_can_view_directory',
                'members_can_manage_optout',
                'members_can_manage_optin',
                'public_description',
                'public_name',
            ]);
        });
    }
}
