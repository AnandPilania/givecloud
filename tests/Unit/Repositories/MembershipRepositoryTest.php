<?php

namespace Tests\Unit\Repositories;

use Ds\Models\Membership;
use Ds\Repositories\MembershipRepository;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class MembershipRepositoryTest extends TestCase
{
    public function testqueryCreatedOrUpdatedAfterPurchase(): void
    {
        $siteCreationDate = site()->created_at;
        $preSiteCreationDate = Carbon::parse($siteCreationDate)->subDay();
        $postSiteCreationDate = Carbon::parse($siteCreationDate)->addDay();

        Membership::factory(2)->create([
            'created_at' => $preSiteCreationDate,
            'updated_at' => $preSiteCreationDate,
        ]);
        $membershipsPostPurchaseDate = Membership::factory(3)
            ->create(['created_at' => $postSiteCreationDate])
            ->merge(Membership::factory(3)->create([
                'created_at' => $postSiteCreationDate,
                'updated_at' => $postSiteCreationDate,
            ]));

        $membershipsFound = $this->app->make(MembershipRepository::class)->queryCreatedOrUpdatedAfterPurchase()->get();

        $this->assertSame($membershipsPostPurchaseDate->count(), $membershipsFound->count());
        $this->assertEquals($membershipsPostPurchaseDate->map->getKey, $membershipsFound->map->getKey);
        $this->assertEquals($membershipsPostPurchaseDate->map->created_at, $membershipsFound->map->created_at);
        $this->assertEquals($membershipsPostPurchaseDate->map->updated_at, $membershipsFound->map->updated_at);
    }
}
