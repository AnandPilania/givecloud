<?php

namespace Tests\Feature\Backend;

use Ds\Common\CDN\Manager as CDN;
use Ds\Domain\Sponsorship\Models\Sponsorship as Sponsee;
use Ds\Events\MediaUploaded;
use Exception;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ImportSponseePhotosControllerTest extends TestCase
{
    public function testIndexView()
    {
        $res = $this->actingAsSuperUser()
            ->get(route('backend.import_sponsee_photos.index'));

        $res->assertOk();
        $res->assertSee('Step 1: Select photos');
    }

    public function testCheckForSponseeMatch()
    {
        $existingSponsee = Sponsee::factory()->create();
        $nonExistantSponsee = Sponsee::factory()->make();

        $existingSponseeFilename = "{$existingSponsee->reference_number}.jpg";
        $nonExistantSponseeFilename = "{$nonExistantSponsee->reference_number}.jpg";

        $res = $this->actingAsSuperUser()
            ->post(route('backend.import_sponsee_photos.check_for_sponsee_match'), [
                'files' => [$existingSponseeFilename, $nonExistantSponseeFilename],
            ]);

        $res->assertOk();
        $res->assertJsonPath('0.name', $existingSponseeFilename);
        $res->assertJsonPath('0.sponsee.reference_number', $existingSponsee->reference_number);
        $res->assertJsonPath('1.name', $nonExistantSponseeFilename);
        $res->assertJsonPath('1.sponsee', null);
    }

    public function testSignUploadUrl()
    {
        $this->partialMock(CDN::class, function ($mock) {
            $mock->shouldReceive('beginSignedUploadSession')->once();
        });

        $nonExistantSponsee = Sponsee::factory()->make();

        $res = $this->actingAsSuperUser()
            ->post(route('backend.import_sponsee_photos.sign_upload_url'), [
                'filename' => "{$nonExistantSponsee->reference_number}.jpg",
            ]);

        $res->assertOk();
        $res->assertJsonStructure(['filename', 'signed_upload_url']);
    }

    public function testSigningUploadUrlWithoutFilename()
    {
        $res = $this->actingAsSuperUser()
            ->post(route('backend.import_sponsee_photos.sign_upload_url'));

        $res->assertStatus(422);
        $res->assertJsonPath('error', 'Filename required.');
    }

    public function testAttachPhotoToSponsee()
    {
        Storage::shouldReceive('disk')->once()->andReturnSelf();
        Storage::shouldReceive('setVisibility')->once()->andReturnTrue();

        $this->expectsEvents(MediaUploaded::class);

        $sponsee = Sponsee::factory()->create();

        $res = $this->actingAsSuperUser()
            ->post(route('backend.import_sponsee_photos.attach_photo_to_sponsee'), [
                'sponsee' => $sponsee->id,
                'filename' => "{$sponsee->reference_number}.jpg",
                'content_type' => 'image/jpeg',
                'size' => 2560,
            ]);

        $sponsee->refresh();

        $res->assertOk();
        $res->assertJsonPath('id', $sponsee->media_id);
    }

    public function testFailToAttachPhotoToSponsee()
    {
        Storage::shouldReceive('disk')->once()->andThrows(new Exception);

        $sponsee = Sponsee::factory()->create();

        $res = $this->actingAsSuperUser()
            ->post(route('backend.import_sponsee_photos.attach_photo_to_sponsee'), [
                'sponsee' => $sponsee->id,
                'filename' => "{$sponsee->reference_number}.jpg",
                'content_type' => 'image/jpeg',
                'size' => 2560,
            ]);

        $res->assertStatus(422);
        $res->assertJsonPath('error', 'Upload failed.');
    }

    public function testAttachPhotoToNonExistantSponsee()
    {
        $res = $this->actingAsSuperUser()
            ->post(route('backend.import_sponsee_photos.attach_photo_to_sponsee'));

        $res->assertStatus(422);
        $res->assertJsonPath('error', 'Sponsee not found.');
    }
}
