<?php

namespace Ds\Domain\MissionControl\Listeners;

use Ds\Domain\MissionControl\MissionControlService;
use Ds\Events\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

class UpdateMissionControlSiteUsers implements ShouldQueue
{
    use Queueable;

    /** @var \Ds\Domain\MissionControl\MissionControlService */
    protected $missionControlService;

    public function __construct(MissionControlService $missionControlService)
    {
        $this->missionControlService = $missionControlService;
    }

    /**
     * @param \Ds\Events\UserCreated|\Ds\Events\UserWasUpdated $event
     */
    public function handle(Event $event): void
    {
        $this->missionControlService->updateSiteUsers($event->user);
    }

    public function viaQueue()
    {
        return 'low';
    }
}
