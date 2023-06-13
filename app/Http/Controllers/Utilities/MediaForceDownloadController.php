<?php

namespace Ds\Http\Controllers\Utilities;

use Ds\Common\CDN\Manager as CDN;
use Ds\Http\Controllers\Controller;
use Ds\Models\Media;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class MediaForceDownloadController extends Controller
{
    protected function registerMiddleware()
    {
        $this->middleware('auth');
        $this->middleware('requires.superUser');
    }

    public function index(): View
    {
        return view('utilities.media-force-download');
    }

    public function autocomplete(Request $request): JsonResponse
    {
        $media = Media::query()
            ->select('id', 'filename')
            ->where('filename', 'like', '%' . $request->input('query') . '%')
            ->take(50)
            ->getQuery()
            ->get();

        return response()->json($media);
    }

    public function update(Request $request, CDN $cdn): RedirectResponse
    {
        try {
            $media = Media::findOrFail($request->input('media_id'));

            $cdn->getObject("{$media->collection_name}/{$media->filename}")->update([
                'contentDisposition' => $request->input('force_download') ? 'attachment' : '',
            ]);

            $this->flash->success('Media successfully updated.');
        } catch (ModelNotFoundException $e) {
            $this->flash->error('No matching media found.');
        } catch (Throwable $e) {
            $this->flash->error('Error occurred while updating media.');
        }

        return redirect()->back();
    }
}
