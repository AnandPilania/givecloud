<?php

namespace Ds\Events;

use Ds\Domain\Theming\Liquid\Drops\PledgeCampaignDrop;
use Ds\Domain\Theming\Liquid\Drops\SocialProofDrop;
use Ds\Illuminate\Broadcasting\Channel;
use Ds\Models\OrderItem;
use Ds\Models\Pledge;
use Ds\Models\PledgeCampaign;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class PledgableAmountsRefresh implements ShouldBroadcast
{
    use SerializesModels;

    /** @var \Ds\Models\PledgeCampaign */
    public $campaign;

    /**
     * Create a new event instance.
     *
     * @param \Ds\Models\PledgeCampaign $campaign
     * @return void
     */
    public function __construct(PledgeCampaign $campaign)
    {
        $this->campaign = $campaign;
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'pledgable_amounts_refresh';
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

    /**
     * Get the data to broadcast.
     *
     * @return array
     */
    public function broadcastWith()
    {
        return [
            'campaign' => new PledgeCampaignDrop($this->campaign),
            'pledgeables' => $this->getPledgeables(),
        ];
    }

    /**
     * Get the most recent pledgeables for the campaign.
     *
     * @return array
     */
    protected function getPledgeables(): array
    {
        $pledgeables = DB::query()
            ->fromSub(
                $this->campaign->orderItems()
                    ->select([
                        DB::raw("'order_item' as type"),
                        'productorderitem.id as id',
                        'productorder.confirmationdatetime as commitment_date',
                    ])->whereNull('productorder.refunded_at')
                    ->unionAll(
                        $this->campaign->pledges()->select([
                            DB::raw("'pledge' as type"),
                            'pledges.id as id',
                            'pledges.created_at as commitment_date',
                        ])
                    ),
                'agg'
            )->orderBy('commitment_date', 'desc')
            ->take(10)
            ->get();

        $items = OrderItem::find($pledgeables->where('type', 'order_item')->pluck('id'))->keyBy('id');
        $pledges = Pledge::find($pledgeables->where('type', 'pledge')->pluck('id'))->keyBy('id');

        return $pledgeables->map(function ($pledgeable) use ($items, $pledges) {
            return $pledgeable->type === 'order_item'
                ? $items[$pledgeable->id] ?? null
                : $pledges[$pledgeable->id] ?? null;
        })->filter()
            ->map(function ($pledgeable) {
                return new SocialProofDrop($pledgeable);
            })->all();
    }
}
