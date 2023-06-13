<?php

namespace Tests\Feature\Jobs;

use Ds\Domain\MissionControl\MissionControlService;
use Ds\Jobs\BroadcastDomainChange;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class BroadcastDomainChangeTest extends TestCase
{
    public function testJobSendsRequestToMissionControl(): void
    {
        Http::fake();
        $site = $this->app->make(MissionControlService::class)->getSite();

        dispatch(new BroadcastDomainChange($site));

        Http::assertSent(function (Request $request) use ($site) {
            return $request->url() == MissionControlService::getMissionControlApiUrl('site/domain-updated/' . $site->id);
        });
    }
}
