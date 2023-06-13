<?php

use Ds\Jobs\CalculateLifetimeMemberGiving;
use Ds\Models\Member;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ResumLifetimeTotalsForSponsorshipOffsets extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $members_to_update = DB::table('fundraising_pages')
            ->select('member_organizer_id')
            ->where('amount_raised_offset', '>', 0)
            ->distinct()
            ->get();

        $members_to_update->each(function ($member) {
            $member = Member::find($member->member_organizer_id);
            CalculateLifetimeMemberGiving::dispatch($member);
        });
    }
}
