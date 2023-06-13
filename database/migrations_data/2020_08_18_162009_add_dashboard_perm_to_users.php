<?php

use Ds\Models\User;
use Illuminate\Database\Migrations\Migration;

class AddDashboardPermToUsers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach (User::all() as $user) {
            if (is_array($user->permissions_json)) {
                $user->permissions_json = array_merge($user->permissions_json, ['dashboard.view']);
                $user->save();
            }
        }
    }
}
