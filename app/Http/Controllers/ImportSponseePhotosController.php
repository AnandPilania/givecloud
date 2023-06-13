<?php

namespace Ds\Http\Controllers;

use Ds\Common\CDN\Manager as CDN;
use Ds\Domain\Sponsorship\Models\Sponsorship as Sponsee;
use Ds\Events\MediaUploaded;
use Ds\Models\Media;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class ImportSponseePhotosController extends Controller
{
    /**
     * Register controller middleware.
     */
    protected function registerMiddleware()
    {
        $this->middleware('auth');
        $this->middleware('requires.superUser');
    }

    public function index(): View
    {
        return view('import.sponsee-photos');
    }

    public function checkForSponseeMatch(Request $request): JsonResponse
    {
        $files = collect($request->input('files'))->map(function ($filename) {
            return [
                'name' => $filename,
                'sponsee' => pathinfo($filename, PATHINFO_FILENAME),
            ];
        });

        $sponsees = DB::table('sponsorship')
            ->select([
                'id',
                DB::raw("CONCAT_WS(' ', first_name, last_name) as name"),
                'reference_number',
                DB::raw('IF(media_id IS NULL, 0, 1) as has_photo'),
            ])->whereIn('reference_number', $files->pluck('sponsee'))
            ->get()
            ->keyBy('reference_number');

        return response()->json($files->map(function ($data) use ($sponsees) {
            return array_merge($data, [
                'sponsee' => $sponsees[$data['sponsee']] ?? null,
            ]);
        }));
    }

    public function signUploadUrl(Request $request, CDN $cdn): JsonResponse
    {
        if (! $request->input('filename')) {
            return response()->json(['error' => 'Filename required.'], 422);
        }

        $filename = Media::getUniqueFilename($request->input('filename'));

        return response()->json([
            'filename' => $filename,
            'signed_upload_url' => $cdn->beginSignedUploadSession(
                "sponsorships/$filename",
                $request->input('content_type')
            ),
        ]);
    }

    public function attachPhotoToSponsee(Request $request): JsonResponse
    {
        try {
            $sponsee = Sponsee::findOrFail($request->input('sponsee'));
        } catch (ModelNotFoundException $e) {
            return response()->json(['error' => 'Sponsee not found.'], 422);
        }

        $filename = sanitize_filename($request->input('filename'));

        try {
            $media = new Media;
            $media->collection_name = 'sponsorships';
            $media->name = pathinfo($filename, PATHINFO_FILENAME);
            $media->filename = $filename;
            $media->content_type = request('content_type');
            $media->size = request('size');
            $media->save();

            Storage::disk('cdn')->setVisibility("sponsorships/{$filename}", 'public');

            event(new MediaUploaded($media));
        } catch (Throwable $e) {
            return response()->json(['error' => 'Upload failed.'], 422);
        }

        $sponsee->media_id = $media->id;
        $sponsee->save();

        return response()->json($media);
    }
}
