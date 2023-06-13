<?php

namespace Ds\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class PostReleaseNotesCommand extends Command
{
    /** @var string */
    protected $signature = 'post-release-notes';

    /** @var string */
    protected $description = 'Post release notes to MissionControl/Slack';

    public function handle(): int
    {
        Http::withToken(config('services.missioncontrol.api_token'))
            ->post('https://' . config('givecloud.missioncontrol_domain') . '/api/v1/release-notes');

        return 0;
    }
}
