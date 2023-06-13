<?php

namespace Ds\Jobs;

use Ds\Events\VirtualEventConfigurationUpdate;
use Ds\Models\VirtualEvent;
use Ds\Models\VirtualEventLiveStream;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;

class MuxWebhook implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;

    /**
     * Payload
     *
     * @var array
     */
    protected $payload;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $type = $this->payload['type'] ?? null;

        if ($type === 'video.live_stream.active') {
            $this->handleActiveStream();
        } elseif ($type === 'video.asset.live_stream_completed') {
            $this->handleStreamComplete();
        } elseif ($type === 'video.asset.ready') {
            $this->handleAssetReady();
        }
    }

    protected function handleActiveStream(): void
    {
        $id = $this->payload['object']['id'] ?? null;
        $liveStream = VirtualEventLiveStream::where('stream_id', $id)->firstOrFail();
        $liveStream->streaming_video_id = $this->payload['data']['playback_ids'][0]['id'] ?? null;
        $liveStream->status = 'active';
        $liveStream->save();
        $virtualEvent = VirtualEvent::find($liveStream->virtual_event_id);
        event(new VirtualEventConfigurationUpdate($virtualEvent));
    }

    protected function handleStreamComplete(): void
    {
        $id = $this->payload['data']['live_stream_id'] ?? null;
        $liveStream = VirtualEventLiveStream::where('stream_id', $id)->firstOrFail();
        $liveStream->playback_video_id = $this->payload['data']['playback_ids'][0]['id'] ?? null;
        $liveStream->status = 'complete';
        $liveStream->save();
        $virtualEvent = VirtualEvent::find($liveStream->virtual_event_id);
        event(new VirtualEventConfigurationUpdate($virtualEvent));
    }

    protected function handleAssetReady(): void
    {
        $id = $this->payload['data']['live_stream_id'] ?? null;
        $liveStream = VirtualEventLiveStream::where('stream_id', $id)->firstOrFail();
        $liveStream->playback_video_id = $this->payload['data']['playback_ids'][0]['id'] ?? null;
        $liveStream->save();
        $virtualEvent = VirtualEvent::find($liveStream->virtual_event_id);
        event(new VirtualEventConfigurationUpdate($virtualEvent));
    }
}
