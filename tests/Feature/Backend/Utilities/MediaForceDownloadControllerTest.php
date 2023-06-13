<?php

namespace Tests\Feature\Backend\Utilities;

use Ds\Common\CDN\Manager as CDN;
use Ds\Models\Media;
use Google\Cloud\Storage\StorageObject;
use Tests\TestCase;

class MediaForceDownloadControllerTest extends TestCase
{
    public function testIndexView()
    {
        $res = $this->actingAsSuperUser()
            ->get(route('backend.utilities.media_force_download.index'));

        $res->assertOk();
        $res->assertSee('Force media to download automatically');
    }

    public function testAutocompleteMatch()
    {
        $media = Media::factory()->jpeg()->create();

        $res = $this->actingAsSuperUser()
            ->post(route('backend.utilities.media_force_download.autocomplete'), ['query' => $media->filename]);

        $res->assertOk();
        $res->assertJsonPath('0.id', $media->getKey());
    }

    public function testUpdateWithNonExistantMedia()
    {
        $res = $this->actingAsSuperUser()
            ->post(route('backend.utilities.media_force_download.update'));

        $res->assertSessionHasFlashMessages(['error' => 'No matching media found.']);
    }

    public function testUpdateSuccessfully()
    {
        $this->partialMock(CDN::class, function ($mock) {
            $storageObjectMock = $this->createPartialMock(StorageObject::class, ['update']);
            $storageObjectMock->expects($this->once())->method('update');

            $mock->shouldReceive('getObject')->once()->andReturn($storageObjectMock);
        });

        $media = Media::factory()->jpeg()->create();

        $res = $this->actingAsSuperUser()
            ->post(route('backend.utilities.media_force_download.update'), ['media_id' => $media->getKey()]);

        $res->assertSessionHasFlashMessages(['success' => 'Media successfully updated.']);
    }
}
