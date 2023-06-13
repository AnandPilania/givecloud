<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class UpdateOrderPaidToContributionPaid extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('hook_events')
            ->where('name', 'order_paid')
            ->update(['name' => 'contribution_paid']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('hook_events')
            ->where('name', 'contribution_paid')
            ->update(['name' => 'order_paid']);
    }
}
