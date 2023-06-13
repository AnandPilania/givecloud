<?php

namespace Ds\Events;

use Ds\Domain\Theming\Liquid\Drops\PledgeCampaignDrop;
use Ds\Illuminate\Broadcasting\Channel;
use Ds\Models\VirtualEvent;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class VirtualEventConfigurationUpdate implements ShouldBroadcastNow
{
    use SerializesModels;

    /** @var \Ds\Domain\Theming\Liquid\Drops\PledgeCampaignDrop */
    public $campaign;

    /** @var array */
    public $event;

    /**
     * Create a new event instance.
     *
     * @param \Ds\Models\VirtualEvent $event
     * @return void
     */
    public function __construct(VirtualEvent $event)
    {
        $this->campaign = new PledgeCampaignDrop($event->campaign);

        $this->event = [
            'name' => $event->name,
            'logo' => $event->logo,
            'background_image' => $event->background_image,
            'theme_style' => $event->theme_style,
            'theme_primary_color' => $event->theme_primary_color,
            'start_date' => $event->start_date,
            'video_source' => $event->video_source,
            'live_stream_status' => $event->live_stream_status, // mux-only
            'video_id' => $event->live_stream_video_id,
            'chat_id' => $event->chat_id,
            'is_amount_tally_enabled' => $event->is_amount_tally_enabled,
            'is_chat_enabled' => $event->is_chat_enabled,
            'is_celebration_enabled' => $event->is_celebration_enabled,
            'is_honor_roll_enabled' => $event->is_honor_roll_enabled,
            'is_emoji_reaction_enabled' => $event->is_emoji_reaction_enabled,
            'celebration_threshold' => $event->celebration_threshold,
            'prestream_message_line_1' => $event->prestream_message_line_1,
            'prestream_message_line_2' => $event->prestream_message_line_2,
            'tab_one_label' => $event->tab_one_label,
            'tab_one_product_id' => $event->productOne->code ?? null,
            'tab_two_label' => $event->tab_two_label,
            'tab_two_product_id' => $event->productTwo->code ?? null,
            'tab_three_label' => $event->tab_three_label,
            'tab_three_product_id' => $event->productThree->code ?? null,
        ];
    }

    /**
     * The event's broadcast name.
     *
     * @return string
     */
    public function broadcastAs()
    {
        return 'configuration_update';
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
