<?php

use Ds\Domain\Sponsorship\Models\Sponsor;
use Ds\Domain\Sponsorship\Models\Sponsorship;
use Ds\Domain\Sponsorship\Services\SponsorCountService;
use Illuminate\Database\Migrations\Migration;

class UpdateSponsorshipCountsWhereMemberIsNull extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Sponsor::query()
            ->where('member_id', 0)
            ->with('sponsorship')
            ->get()
            ->pluck('sponsorship')
            ->unique()
            ->each(function (Sponsorship $sponsorship) {
                app(SponsorCountService::class)->update($sponsorship);
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Nope.
    }
}
