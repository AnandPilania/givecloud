<?php

namespace Ds\Http\Controllers;

use Carbon\Carbon;
use Ds\Domain\Settings\Integrations\Config\GoCardlessIntegrationSettingsConfig;
use Ds\Domain\Settings\Integrations\Config\PayPalIntegrationSettingsConfig;
use Ds\Domain\Settings\Integrations\Config\PaySafeIntegrationSettingsConfig;
use Ds\Domain\Shared\DataTable;
use Ds\Enums\VirtualEventThemePrimaryColor;
use Ds\Enums\VirtualEventThemeStyle;
use Ds\Events\VirtualEventConfigurationUpdate;
use Ds\Http\Requests\VirtualEventPostRequest;
use Ds\Models\PledgeCampaign;
use Ds\Models\VirtualEvent;
use Ds\Services\LiveStreamService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class VirtualEventsController extends Controller
{
    public function index()
    {
        user()->canOrRedirect('virtualevents.view');

        return view('virtual_events.index', [
            '__menu' => 'products.virtualevents',
            'pageTitle' => 'Virtual Events',
            'event_count' => VirtualEvent::count(),
        ]);
    }

    public function index_ajax()
    {
        if (! user()->can('virtualevents.view')) {
            return false;
        }

        $virtualevents = VirtualEvent::query();

        $dataTable = new DataTable($virtualevents, [
            'id',
            'name',
            'start_date',
            'background_image',
        ]);

        $dataTable->setFormatRowFunction(function ($virtualevent) {
            return [
                dangerouslyUseHTML(
                    '<a class="meta-img" href="/jpanel/virtual-events/' . e($virtualevent->id) . '/edit">' .
                        '<div class="avatar-xl" style="background-image:url(\'' . e($virtualevent->background_image) . '\');"></div>' .
                    '</a>' .
                    '<div class="meta-desc">' .
                        // '<div class="meta-pre">' . $virtualevent->name . '</div>' .
                        '<div class="meta-pre">&nbsp;</div>' .
                        '<div class="title"><a href="/jpanel/virtual-events/' . e($virtualevent->id) . '/edit">' . e($virtualevent->name) . '</a></div>' .
                    '</div>'
                ),
                e($virtualevent->start_date->format('M j, Y')),
            ];
        });

        // return datatable JSON
        return response($dataTable->make());
    }

    public function edit($virtualEventId = null, GoCardlessIntegrationSettingsConfig $gocardlessIntegration, PayPalIntegrationSettingsConfig $paypalIntegration, PaySafeIntegrationSettingsConfig $paysafeIntegration)
    {
        user()->canOrRedirect('virtualevents.edit');

        $isNew = $virtualEventId === null ? 1 : 0;

        if ($isNew) {
            $event = new VirtualEvent();
            $event->is_chat_enabled = true;
            $event->is_amount_tally_enabled = true;
            $event->is_celebration_enabled = true;
            $event->is_honor_roll_enabled = true;
            $event->is_emoji_reaction_enabled = true;
            $event->theme_style = VirtualEventThemeStyle::default();
            $event->theme_primary_color = VirtualEventThemePrimaryColor::default();
        } else {
            $event = VirtualEvent::findOrFail($virtualEventId);
        }

        return view('virtual_events.edit', [
            '__menu' => 'products.virtualevents',
            'event' => $event,
            'isNew' => $isNew,
            'base_url' => secure_site_url('virtual-event'),
            'hasStream' => (bool) $event->liveStream,
            'streamUrl' => config('services.mux.stream_url'),
            'streamKey' => $event->liveStream->stream_key ?? '',
            'gocardlessInstalled' => $gocardlessIntegration->isInstalled(),
            'paypalInstalled' => $paypalIntegration->isInstalled(),
            'paysafeInstalled' => $paysafeIntegration->isInstalled(),
            'theme_styles' => VirtualEventThemeStyle::labels(),
            'theme_primary_colors' => VirtualEventThemePrimaryColor::labels(),
        ]);
    }

    public function save(VirtualEventPostRequest $request, LiveStreamService $streamService)
    {
        $isNew = (bool) ! $request->input('id');

        if ($isNew) {
            $event = new VirtualEvent();
            $event->slug = Str::slug($request->input('name'), '-');
            $event->is_celebration_enabled = true;
            $event->is_honor_roll_enabled = true;
            $event->is_emoji_reaction_enabled = true;
            $event->is_chat_enabled = true;
            $event->theme_style = VirtualEventThemeStyle::default();
            $event->theme_primary_color = VirtualEventThemePrimaryColor::default();
            $event->is_amount_tally_enabled = true;
            $event->celebration_threshold = 1000;
        } else {
            $event = VirtualEvent::findOrFail($request->input('id'));
        }

        $event->name = $request->input('name');
        $event->logo = $request->input('logo');
        $event->background_image = $request->input('background_image');
        $event->theme_style = $request->input('theme_style');
        $event->theme_primary_color = $request->input('theme_primary_color');
        $event->start_date = Carbon::parse($request->input('start_date'));
        $event->video_source = $request->input('video_source');
        $event->video_id = $request->input('video_id');
        $event->chat_id = $request->input('chat_id');
        $event->is_amount_tally_enabled = $request->input('is_amount_tally_enabled');
        $event->is_chat_enabled = $request->input('is_chat_enabled');
        $event->is_celebration_enabled = $request->input('is_celebration_enabled');
        $event->is_honor_roll_enabled = $request->input('is_honor_roll_enabled');
        $event->is_emoji_reaction_enabled = $request->input('is_emoji_reaction_enabled');
        $event->celebration_threshold = $request->input('celebration_threshold');
        $event->prestream_message_line_1 = $request->input('prestream_message_line_1');
        $event->prestream_message_line_2 = $request->input('prestream_message_line_2');
        $event->tab_one_label = $request->input('tab_one_label');
        $event->tab_one_product_id = $request->input('tab_one_product_id');
        $event->tab_two_label = $request->input('tab_two_label');
        $event->tab_two_product_id = $request->input('tab_two_product_id');
        $event->tab_three_label = $request->input('tab_three_label');
        $event->tab_three_product_id = $request->input('tab_three_product_id');

        $campaign = $this->_createCampaign($event);
        $event->campaign_id = $campaign->id;
        $event->save();

        if ($event->video_source === 'mux' && ! $event->liveStream) {
            $streamService->createLiveStream($event->id);
        }

        $event->refresh();
        $this->_updateCampaign($event, $campaign);

        if ($isNew) {
            $this->flash->success('Your event has been setup!');
        } else {
            $this->flash->success('Your event has been updated!');
        }

        event(new VirtualEventConfigurationUpdate($event));

        return response()->json([
            'id' => $event->id,
        ]);
    }

    public function updateSlug(Request $request, $virtualEventId)
    {
        if (! user()->can('virtualevents.edit')) {
            return false;
        }

        $event = VirtualEvent::findOrFail($virtualEventId);
        $event->slug = $request->input('slug');
        $event->save();

        return response()->json([
            'id' => $event->id,
            'slug' => $event->slug,
        ]);
    }

    public function destroy($virtualEventId)
    {
        if (! user()->can('virtualevents.edit')) {
            return false;
        }

        $event = VirtualEvent::findOrFail($virtualEventId);

        $event->delete();

        return response()->json([
            'id' => $event->id,
        ]);
    }

    protected function _createCampaign($event)
    {
        if (! $event->campaign_id) {
            $campaign = new PledgeCampaign();
            $campaign->name = 'Virtual Event: ' . $event->name;
            $campaign->save();
        } else {
            $campaign = PledgeCampaign::find($event->campaign_id);
        }

        return $campaign;
    }

    protected function _updateCampaign($event, $campaign)
    {
        $newProductIds = [];
        if ($event->tab_one_product_id) {
            $newProductIds[] = $event->tab_one_product_id;
        }
        if ($event->tab_two_product_id) {
            $newProductIds[] = $event->tab_two_product_id;
        }
        if ($event->tab_three_product_id) {
            $newProductIds[] = $event->tab_three_product_id;
        }

        $difference = $campaign->products()->sync($newProductIds);

        // If there are changes, recalculate the pledges
        if (count($difference['attached']) > 0 || count($difference['detached']) > 0) {
            $campaign->calculate(true);
        }
    }
}
