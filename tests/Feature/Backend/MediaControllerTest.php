<?php

namespace Tests\Feature\Backend;

use Ds\Common\CDN\Manager as CDN;
use Ds\Events\MediaUploaded;
use Ds\Models\Media;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MediaControllerTest extends TestCase
{
    /**
     * @dataProvider signingACdnUploadUrlProvider
     */
    public function testSigningACdnUploadUrl(?string $filename, ?string $collectionName, ?string $contentType, string $expectedCollectionName, bool $expectOk)
    {
        if ($filename) {
            $filename = Media::getUniqueFilename($filename);
        }

        $signedUrl = 'https://example.com/signed_url';

        if ($expectOk) {
            $this->partialMock(CDN::class, function ($mock) use ($expectedCollectionName, $filename, $contentType, $signedUrl) {
                $mock->shouldReceive('beginSignedUploadSession')
                    ->once()
                    ->with("$expectedCollectionName/$filename", $contentType)
                    ->andReturn($signedUrl);
            });
        }

        $res = $this->actingAsSuperUser()
            ->post(route('backend.media.cdn_sign'), [
                'filename' => $filename,
                'collection_name' => $collectionName,
                'content_type' => $contentType,
            ]);

        if ($expectOk) {
            $res->assertOk();
            $res->assertJsonPath('filename', $filename);
            $res->assertJsonPath('signed_upload_url', $signedUrl);
        } else {
            $res->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
            $res->assertJsonPath('error', 'Upload failed.');
        }
    }

    public function signingACdnUploadUrlProvider(): array
    {
        return [
            ['photo.jpg', null, null, 'files', true],
            ['photo.jpg', 'sponsorship', 'image/png', 'sponsorships', true],
            [null, null, null, 'files', false],
        ];
    }

    /**
     * @dataProvider completingMediaUploadProvider
     */
    public function testCompletingMediaUpload(?string $filename, ?string $collectionName, ?string $contentType, bool $expectOk)
    {
        if ($filename) {
            $filename = Media::getUniqueFilename($filename);
        }

        if ($expectOk) {
            Storage::shouldReceive('disk')->once()->andReturnSelf();
            Storage::shouldReceive('setVisibility')->once()->andReturnTrue();

            $this->expectsEvents(MediaUploaded::class);
        }

        $res = $this->actingAsSuperUser()
            ->post(route('backend.media.cdn_done'), [
                'filename' => $filename,
                'collection_name' => $collectionName,
                'content_type' => $contentType,
            ]);

        if ($expectOk) {
            $res->assertOk();
            $res->assertJsonPath('filename', $filename);
        } else {
            $res->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY);
            $res->assertJsonPath('error', 'Upload failed.');
        }
    }

    public function completingMediaUploadProvider(): array
    {
        return [
            ['photo.jpg', null, null, true],
            ['photo.jpg', 'sponsorship', 'image/png', true],
            [null, null, null, false],
        ];
    }
}
