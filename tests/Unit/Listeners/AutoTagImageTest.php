<?php

namespace Tests\Unit\Listeners;

use Ds\Events\MediaUploaded;
use Ds\Listeners\AutoTagImage;
use Ds\Models\Media;
use Ds\Services\GoogleVisionService;
use Faker\Generator as Faker;
use Tests\TestCase;

class AutoTagImageTest extends TestCase
{
    public function testShouldQueueOnlyImages()
    {
        $autoTagImageListener = app(AutoTagImage::class);

        $this->assertTrue($autoTagImageListener->shouldQueue(
            new MediaUploaded(Media::factory()->jpeg()->make())
        ));

        $this->assertFalse($autoTagImageListener->shouldQueue(
            new MediaUploaded(Media::factory()->pdf()->make())
        ));
    }

    public function testAddingTagsToMedia()
    {
        $tags = array_unique(app(Faker::class)->words(10));

        $this->partialMock(GoogleVisionService::class, function ($mock) use ($tags) {
            $mock->shouldReceive('getImageLabels')->once()->andReturn($tags);
        });

        $media = Media::factory()->jpeg()->create();

        app(AutoTagImage::class)->handle(new MediaUploaded($media));

        $this->assertSame(count($tags), $media->tags()->whereIn('name', $tags)->count());
    }
}
