<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AddBillingCycleAnchorToRecurringPaymentProfilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('recurring_payment_profiles', function (Blueprint $table) {
            $table->date('billing_cycle_anchor')->nullable()->after('billing_frequency');
        });

        $this->updateBillingCycleAnchors();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('recurring_payment_profiles', function (Blueprint $table) {
            $table->dropColumn('billing_cycle_anchor');
        });
    }

    private function updateBillingCycleAnchors(): void
    {
        // previous to the implementation of an explicit anchor the next
        // billing dates were basically anchored to the previous billing date
        DB::table('recurring_payment_profiles')->update([
            'billing_cycle_anchor' => DB::raw('next_billing_date'),
        ]);
    }
}
