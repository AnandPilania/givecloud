<?php

namespace Ds\Listeners;

use Ds\Events\MediaUploaded;
use Ds\Models\Tag;
use Ds\Services\GoogleVisionService;

class AutoTagImage
{
    /** @var \Ds\Services\GoogleVisionService */
    protected $googleVision;

    public function __construct(GoogleVisionService $googleVision)
    {
        $this->googleVision = $googleVision;
    }

    /**
     * Handle the event.
     *
     * @param \Ds\Events\MediaUploaded $event
     * @return void
     */
    public function handle(MediaUploaded $event)
    {
        $tags = array_map(function ($name) {
            return compact('name');
        }, $this->googleVision->getImageLabels($event->item->internal_cdn_uri));

        // Using upsert to avoid potential race conditions involved when using
        // firstOrCreate/etc while concurrently tagging images across multiple workers
        Tag::upsert($tags, ['name']);

        $tagIds = Tag::whereIn('name', collect($tags)->pluck('name'))->pluck('id');

        $event->item->tags()->syncWithoutDetaching($tagIds);
    }

    public function shouldQueue(MediaUploaded $event): bool
    {
        return $event->item->is_image;
    }

    public function viaQueue()
    {
        return 'low';
    }
}
