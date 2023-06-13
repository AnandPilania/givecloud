<?php

namespace Ds\Http\Controllers\Frontend;

use Ds\Domain\Commerce\Currency;
use Ds\Models\VirtualEvent;

class VirtualEventController extends Controller
{
    public function index($code, $type = 'full')
    {
        $event = VirtualEvent::where('slug', $code)->firstOrFail();

        $cssFiles = [
            app_asset_url('virtual-events/css/app.css'),
            jpanel_asset_url('dist/css/tailwind.css'),
        ];

        $script_Files = [
            app_asset_url('virtual-events/js/vendor.js'),
            app_asset_url('virtual-events/js/app.js'),
        ];

        $tabs = [];

        if ($event->tab_one_label && $event->productOne) {
            $tabs[] = [
                'name' => $event->tab_one_label,
                'productId' => $event->productOne->code,
                'productSummary' => $event->productOne->summary,
            ];
        }

        if ($event->tab_two_label && $event->productTwo) {
            $tabs[] = [
                'name' => $event->tab_two_label,
                'productId' => $event->productTwo->code,
                'productSummary' => $event->productTwo->summary,
            ];
        }

        if ($event->tab_three_label && $event->productThree) {
            $tabs[] = [
                'name' => $event->tab_three_label,
                'productId' => $event->productThree->code,
                'productSummary' => $event->productThree->summary,
            ];
        }

        $has_compatible_browser = (isset($_SERVER['HTTP_USER_AGENT']) && (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false)) ? false : true;

        return $this->renderTemplate('~virtual-events/index', [
            'type' => $type,
            'currency' => new Currency(),
            'event_code' => $code,
            'logo' => $event->logo,
            'bg_image' => $event->background_image,
            'theme_style' => $event->theme_style,
            'theme_primary_color' => $event->theme_primary_color,
            'css_files' => $cssFiles,
            'script_files' => $script_Files,
            'page_title' => $event->name,
            'updates_total_text' => $event->metadata['updates_total_text'] ?? '',
            'updates_total_text_colour' => $event->metadata['updates_total_text_colour'] ?? '',
            'is_demo_mode_enabled' => $event->is_demo_mode_enabled ? 1 : 0,
            'is_amount_tally_enabled' => $event->is_amount_tally_enabled ? 1 : 0,
            'is_chat_enabled' => $event->is_chat_enabled ? 1 : 0,
            'is_celebration_enabled' => $event->is_celebration_enabled ? 1 : 0,
            'is_honor_roll_enabled' => $event->is_honor_roll_enabled ? 1 : 0,
            'is_emoji_reaction_enabled' => $event->is_emoji_reaction_enabled ? 1 : 0,
            'live_stream_status' => $event->live_stream_status,
            'prestream_message_line_1' => $event->prestream_message_line_1,
            'prestream_message_line_2' => $event->prestream_message_line_2,
            'celebration_threshold' => $event->celebration_threshold,
            'pledge_campaign_id' => $event->campaign_id,
            'pusher_channel' => sys_get('ds_account_name') . '.pledge-campaign.' . $event->campaign_id,
            'tabs' => $tabs,
            'video_id' => $event->live_stream_video_id,
            'chat_id' => $event->chat_id,
            'video_provider' => $event->video_source,
            'has_compatible_browser' => $has_compatible_browser,
            'show_branding' => false,
        ]);
    }
}
