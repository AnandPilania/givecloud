<?php

use Ds\Models\User;
use Illuminate\Database\Migrations\Migration;

class UpdateNotifyRecurringBatchSummaryForAccountOwners extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        User::query()
            ->where('is_account_admin', 1)
            ->notSuperUser()
            ->update([
                'notify_recurring_batch_summary' => 1,
            ]);
    }
}
