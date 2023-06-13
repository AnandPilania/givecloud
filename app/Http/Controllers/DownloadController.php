<?php

namespace Ds\Http\Controllers;

use Ds\Models\Download;
use Ds\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Throwable;

class DownloadController extends Controller
{
    public function getFiles()
    {
        return response()->json(File::all());
    }

    public function download(File $file)
    {
        return redirect()->to($file->temporary_url);
    }

    public function destroy(File $file)
    {
        if ($file->variants()->count()) {
            abort(422);
        }

        $file->delete();
    }

    /**
     * Get a signed upload URL.
     */
    public function cdnSign(Request $request)
    {
        if (empty(request('filename'))) {
            return response(['message' => 'Filename is required.'], 422);
        }

        $filename = File::getUniqueFilename(request('filename'));

        return [
            'filename' => $filename,
            'signed_upload_url' => app('cdn')->beginSignedUploadSession("downloads/$filename", request('content_type')),
        ];
    }

    /**
     * Create Media from uploaded content.
     */
    public function cdnDone(Request $request)
    {
        $filename = sanitize_filename(request('filename'));

        try {
            $file = new File;
            $file->name = pathinfo($filename, PATHINFO_FILENAME);
            $file->filename = $filename;
            $file->content_type = request('content_type');
            $file->size = request('size');
            $file->save();

            Storage::disk('cdn')->setVisibility("downloads/{$file->filename}", 'private');
            event(new \Ds\Events\MediaUploaded($file));
        } catch (Throwable $e) {
            return response(['error' => 'Upload failed.'], 422);
        }

        if ($file) {
            return response()->json($file);
        }

        return response(['error' => 'Upload failed.'], 422);
    }

    public function autocomplete()
    {
        $keywords = request()->input('query');

        $files = \Ds\Models\File::query();

        if (request()->input('query')) {
            $files->where('filename', 'like', '%' . $keywords . '%');
        }

        $file_json = [];

        foreach ($files->get() as $file) {
            $file_json[] = (object) [
                'id' => $file->id,
                'name' => $file->filename,
                'url' => $file->download_url,
                'size_formatted' => numeralFormat($file->size, '0[.]0b'),
                'extension' => pathinfo($file->filename, PATHINFO_EXTENSION),
                'fa_icon' => $file->fa_icon,
            ];
        }

        return response($file_json);
    }
}
