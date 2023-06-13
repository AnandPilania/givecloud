<?php

namespace Ds\Services;

use Ds\Models\VirtualEventLiveStream;
use Illuminate\Support\Facades\Http;
use MuxPhp\Api\LiveStreamsApi;
use MuxPhp\Configuration;
use MuxPhp\Models\CreateAssetRequest;
use MuxPhp\Models\CreateLiveStreamRequest;
use MuxPhp\Models\PlaybackPolicy;

class LiveStreamService
{
    /**
     * Create a Live Stream.
     */
    public function createLiveStream(int $eventId): void
    {
        $this->config = Configuration::getDefaultConfiguration()
            ->setUsername(config('services.mux.token_id'))
            ->setPassword(config('services.mux.secret_key'));

        $this->liveApi = new LiveStreamsApi(
            Http::buildClientForDirectUsage(),
            $this->config
        );

        $createAssetRequest = new CreateAssetRequest(['playback_policy' => [PlaybackPolicy::PUBLIC_PLAYBACK_POLICY]]);
        $createLiveStreamRequest = new CreateLiveStreamRequest([
            'playback_policy' => [PlaybackPolicy::PUBLIC_PLAYBACK_POLICY],
            'new_asset_settings' => $createAssetRequest,
            'passthrough' => sys_get('ds_account_name'),
            'reconnect_window' => 60,
        ]);
        $stream = $this->liveApi->createLiveStream($createLiveStreamRequest);

        $playbackData = array_map(function ($playbackId) {
            return [
                'id' => $playbackId->getId(),
                'policy' => $playbackId->getPolicy(),
            ];
        }, $stream->getData()->getPlaybackIds());

        $livestream = new VirtualEventLiveStream();
        $livestream->virtual_event_id = $eventId;
        $livestream->stream_id = $stream->getData()->getId();
        $livestream->stream_key = $stream->getData()->getStreamKey();
        $livestream->streaming_video_id = $playbackData[0]['id'];
        $livestream->status = 'idle';
        $livestream->save();
    }
}
