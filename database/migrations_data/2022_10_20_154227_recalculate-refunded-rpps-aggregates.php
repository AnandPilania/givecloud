<?php

use Ds\Illuminate\Console\ProgressBar;
use Ds\Models\RecurringPaymentProfile;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Migrations\Migration;

class RecalculateRefundedRppsAggregates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $rpps = RecurringPaymentProfile::query()->whereHas('transactions', function (Builder $query) {
            $query->refunded();
        });

        $progress = new ProgressBar($rpps->count());
        $progress->start();

        $rpps->lazy(50)->each(function (RecurringPaymentProfile $rpp) use ($progress) {
            $rpp->refreshAggregateAmount();
            $progress->advance();
        });

        $progress->finish();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Never gonna give you up
        // Never gonna let you down
        // Never gonna run around and desert you
        // Never gonna make you cry
        // Never gonna say goodbye
        // Never gonna tell a lie and hurt you
    }
}
