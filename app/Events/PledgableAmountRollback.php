<?php

namespace Ds\Events;

use Ds\Domain\Theming\Liquid\Drops\PledgeCampaignDrop;
use Ds\Domain\Theming\Liquid\Drops\SocialProofDrop;
use Ds\Illuminate\Broadcasting\Channel;
use Ds\Illuminate\Database\Eloquent\Model;
use Ds\Models\PledgeCampaign;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class PledgableAmountRollback implements ShouldBroadcastNow
{
    use SerializesModels;

    /** @var \Ds\Domain\Theming\Liquid\Drops\PledgeCampaignDrop */
    public $campaign;

    /** @var \Ds\Domain\Theming\Liquid\Drops\SocialProofDrop */
    public $pledgable;

    /**
     * Create a new event instance.
     *
     * @param \Ds\Models\PledgeCampaign $campaign
     * @param \Ds\Illuminate\Database\Eloquent\Model $pledgable
     * @return void
     */
    public function __construct(PledgeCampaign $campaign, Model $pledgable)
    {
        $this->campaign = new PledgeCampaignDrop($campaign);
        $this->pledgable = new SocialProofDrop($pledgable);
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'pledgable_amount_rollback';
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
