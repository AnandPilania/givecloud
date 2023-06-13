<?php

namespace Ds\Http\Controllers;

use Ds\Domain\Theming\Liquid\Drop;
use Ds\Models\Media;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ImageController extends Controller
{
    public function destroy()
    {
        user()->canOrRedirect('file.edit');

        $media = \Ds\Models\Media::findOrFail(request('id'));
        $media->delete();

        rescueQuietly(fn () => Storage::disk('cdn')->delete("{$media->collection_name}/{$media->filename}"));

        return ['success' => true];
    }

    public function directory_listing()
    {
        user()->canOrRedirect('file.view');

        $images = Media::collection('files')
            ->orderBy('created_at', 'desc');

        request()->whenFilled('query', fn ($query) => $images->where('filename', 'like', "%$query%"));

        $paginator = $images->paginate(42);

        $data = Drop::resolveData($paginator)->toArray();
        $data['data'] = $paginator->items();

        return $data;
    }

    /**
     * Upload and save images.
     */
    public function imageUpload(Request $request)
    {
        try {
            $media = Media::storeUpload('file');
        } catch (Throwable $e) {
            return response(['error' => 'Upload failed.'], 422);
        }

        if ($media) {
            return response([
                'id' => $media->id,
                'filename' => $media->filename,
                'public_url' => $media->public_url,
                'thumbnail_url' => $media->thumbnail_url,

                // TinyMCE expects a location key
                'location' => $media->public_url,
            ]);
        }

        return response(['error' => 'Upload failed.'], 422);
    }

    /**
     * Image proxy to enable editing of externally hosted images.
     */
    public function imageTools()
    {
        $url = request('url');

        if (empty($url) || ! filter_var($url, FILTER_VALIDATE_URL)) {
            abort(404);
        }

        if (! preg_match('@^https?://@i', $url)) {
            abort(404);
        }

        $ext = pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION);

        if ($ext === 'gif' || $ext === 'png') {
            header("Content-Type: image/$ext");
        } else {
            header('Content-Type: image/jpeg');
        }

        $context = stream_context_create([
            'http' => ['user_agent' => 'Mozilla/5.0 (Windows; U; Windows NT 5.1; de; rv:1.9.0.7) Gecko/2009021910 Firefox/3.0.7'],
        ]);

        readfile($url, false, $context);
        exit;
    }
}
