<?php

namespace Ds\Http\Controllers;

use Ds\Events\MediaUploaded;
use Ds\Http\Requests\MediaSignedUploadUrlRequest;
use Ds\Models\Media;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Throwable;

class MediaController extends Controller
{
    /**
     * List all media
     */
    public function list()
    {
        $media = Media::query('media.*')
            ->join('mediables', 'mediables.media_id', '=', 'media.id')
            ->where('mediables.mediable_type', request()->input('parent_type'))
            ->where('mediables.mediable_id', request()->input('parent_id'))
            ->get();

        return response()->json($media);
    }

    /**
     * Get a signed upload URL.
     */
    public function cdnSign(MediaSignedUploadUrlRequest $request)
    {
        $filename = Media::getUniqueFilename(sanitize_filename($request->filename));
        $collectionName = Str::plural($request->collection_name ?? 'files');

        return [
            'filename' => $filename,
            'signed_upload_url' => app('cdn')->beginSignedUploadSession("$collectionName/$filename", $request->content_type),
        ];
    }

    /**
     * Create Media from uploaded content.
     */
    public function cdnDone(MediaSignedUploadUrlRequest $request)
    {
        try {
            $media = new Media;
            $media->collection_name = Str::plural($request->collection_name ?? 'files');
            $media->filename = Media::getUniqueFilename(sanitize_filename($request->filename));
            $media->name = pathinfo($media->filename, PATHINFO_FILENAME);
            $media->content_type = $request->content_type;
            $media->size = (int) $request->size;
            $media->saveOrFail();

            $media->setVisibility('public');

            event(new MediaUploaded($media));
        } catch (Throwable $e) {
            return response(['error' => 'Upload failed.'], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()->json($media);
    }

    /**
     * Destroy an alias record
     */
    public function destroy($media_id)
    {
        Media::findOrFail($media_id)->delete();

        return response()->json(true);
    }
}
