<?php

namespace Ds\Domain\Sponsorship\Services;

use Ds\Domain\Sponsorship\Models\Sponsorship;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class SponsorCountService
{
    /** @var int */
    private const LIVING_WATERS_CLIENT_ID = 275;

    public function update(Sponsorship $sponsorship): bool
    {
        $sponsorship->sponsor_count = $this->getSponsorCount($sponsorship);
        $sponsorship->updateIsSponsored(false);

        return $sponsorship->saveQuietly();
    }

    private function getSponsorCount(Sponsorship $sponsorship): int
    {
        // EXCEPTIONAL EXCEPTION!! Having sponors that spans multiple sites (i.e. databases) is
        // functionality written specifically for LWI and not offered to any other subscribers.
        // To be clear this is HACKERY. The alternative would be moving the `sponsorship_database_name`
        // config to a new column on the sites table in MC.
        if (site()->client->id === static::LIVING_WATERS_CLIENT_ID) {
            return $this->getLivingWatersSponsorCount($sponsorship);
        }

        return $sponsorship->activeSponsors()->whereHas('member')->count();
    }

    private function getLivingWatersSponsorCount(Sponsorship $sponsorship): int
    {
        return DB::query()->fromSub(
            $this->getBuilderForSiteSponsors('lwi-aac', $sponsorship)
                ->union($this->getBuilderForSiteSponsors('aac-ca', $sponsorship))
                ->union($this->getBuilderForSiteSponsors('aac-de', $sponsorship))
                ->union($this->getBuilderForSiteSponsors('aac-nz', $sponsorship))
                ->union($this->getBuilderForSiteSponsors('aac-uk', $sponsorship)),
            'sponsors'
        )->count('sponsors.id');
    }

    private function getBuilderForSiteSponsors(string $databaseName, Sponsorship $sponsorship): Builder
    {
        return DB::table("$databaseName.sponsors")
            ->where('sponsorship_id', $sponsorship->getKey())
            ->whereNull('ended_at')
            ->whereNull('deleted_at');
    }
}
