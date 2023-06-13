<?php

use Ds\Domain\Sponsorship\Models\Sponsorship;
use Illuminate\Database\Migrations\Migration;

class EnsureSponsorshipCountsAreCorrect extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (empty(sys_get('sponsorship_max_sponsors'))) {
            sys_set('sponsorship_max_sponsors', sys_get('sponsorship_num_sponsors'));
        }

        try {
            Sponsorship::updateAllIsSponsored();
        } catch (Throwable $e) {
            // do nothing
        }
    }
}
