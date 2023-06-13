<?php

namespace Ds\Jobs;

use Ds\Domain\MissionControl\MissionControlService;
use Ds\Domain\MissionControl\Models\Site;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;

class BroadcastDomainChange implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    protected Site $site;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Site $site)
    {
        $this->site = $site;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $url = MissionControlService::getMissionControlApiUrl('site/domain-updated/' . $this->site->id);
        Http::withToken(config('services.missioncontrol.api_token'))
            ->post($url);
    }
}
