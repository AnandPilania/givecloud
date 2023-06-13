<?php

namespace Tests\Feature\Backend\Api;

use Ds\Models\VirtualEventLiveStream;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * @group api
 */
class MuxWebhookTest extends TestCase
{
    public function testLiveStreamActiveWebhook(): void
    {
        $payload = json_decode(File::get(base_path('tests/fixtures/mux/video.live_stream.active.json')));
        $virtualEventLiveStream = VirtualEventLiveStream::factory()->create([
            'stream_id' => $payload->object->id,
        ]);

        $this->postJson(
            route('webhook.mux'),
            (array) $payload
        );

        $virtualEventLiveStream->refresh();
        $this->assertSame($virtualEventLiveStream->stream_id, $payload->object->id);
        $this->assertSame($virtualEventLiveStream->status, 'active');
        $this->assertSame($virtualEventLiveStream->streaming_video_id, $payload->data->playback_ids[0]->id);
    }

    public function testLiveStreamCompletedWebhook(): void
    {
        $payload = json_decode(File::get(base_path('tests/fixtures/mux/video.asset.live_stream_completed.json')));
        $virtualEventLiveStream = VirtualEventLiveStream::factory()->create([
            'stream_id' => $payload->data->live_stream_id,
        ]);

        $this->postJson(
            route('webhook.mux'),
            (array) $payload
        );

        $virtualEventLiveStream->refresh();
        $this->assertSame($virtualEventLiveStream->stream_id, $payload->data->live_stream_id);
        $this->assertSame($virtualEventLiveStream->status, 'complete');
        $this->assertSame($virtualEventLiveStream->playback_video_id, $payload->data->playback_ids[0]->id);
    }

    public function testLiveStreamRecordingReadyWebhook(): void
    {
        $payload = json_decode(File::get(base_path('tests/fixtures/mux/video.asset.ready.json')));
        $virtualEventLiveStream = VirtualEventLiveStream::factory()->create([
            'stream_id' => $payload->data->live_stream_id,
        ]);

        $this->postJson(
            route('webhook.mux'),
            (array) $payload
        );

        $virtualEventLiveStream->refresh();
        $this->assertSame($virtualEventLiveStream->stream_id, $payload->data->live_stream_id);
        $this->assertSame($virtualEventLiveStream->playback_video_id, $payload->data->playback_ids[0]->id);
    }
}
