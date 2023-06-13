<?php

namespace Ds\Events;

use Ds\Domain\Theming\Liquid\Drops\PledgeCampaignDrop;
use Ds\Illuminate\Broadcasting\Channel;
use Ds\Models\PledgeCampaign;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class VirtualEventReaction implements ShouldBroadcastNow
{
    use SerializesModels;

    /** @var \Ds\Domain\Theming\Liquid\Drops\PledgeCampaignDrop */
    public $campaign;

    /** @var string */
    public $reaction;

    /**
     * Create a new event instance.
     *
     * @param \Ds\Models\PledgeCampaign $campaign
     * @param string $reaction
     * @return void
     */
    public function __construct(PledgeCampaign $campaign, string $reaction)
    {
        $this->campaign = new PledgeCampaignDrop($campaign);
        $this->reaction = $reaction;
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'reaction';
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Ds\Illuminate\Broadcasting\Channel
     */
    public function broadcastOn()
    {
        return new Channel("pledge-campaign.{$this->campaign->id}");
    }
}
