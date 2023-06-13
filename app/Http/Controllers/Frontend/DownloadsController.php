<?php

namespace Ds\Http\Controllers\Frontend;

use Illuminate\Support\Arr;
use Throwable;

class DownloadsController extends Controller
{
    public function show($filename)
    {
        $filename = urldecode(request('filename'));

        if (request('uid')) {
            $file = \Ds\Models\File::where('_rackspace_uid', request('uid'))->first();
        } elseif ($filename) {
            $file = \Ds\Models\File::where('filename', request('filename'))->first();
        }

        if (empty($file)) {
            abort(404);
        }

        return redirect()->to($file->temporary_url);
    }

    public function product()
    {
        // Retrieve product id
        $product_id = app('hashids')->decode(request('o'));

        if (count($product_id) === 1) {
            $product_id = (int) Arr::get($product_id, 0);
        } else {
            abort(404);
        }

        $download = \Ds\Models\OrderItemFile::find($product_id);

        if (! $download || ! $download->file) {
            return '<h1>' . __('frontend/downloads.download_not_found') . '</h1>';
        }

        $addresses = (array) json_decode($download->addresses);

        if ($download->expiration > -1 && time() > $download->expiration) {
            return '<h1>' . __('frontend/downloads.download_expired') . '</h1>';
        }

        if ($download->address_limit > -1 && count($addresses) >= $download->address_limit) {
            return '<h1>' . __('frontend/downloads.addresses_limit_reached') . '</h1>';
        }

        if ($download->download_limit > -1 && $download->accessed >= $download->download_limit) {
            return '<h1>' . __('frontend/downloads.download_limit_reached') . '</h1>';
        }

        $addresses[request()->ip()] = 1;

        $download->accessed++;
        $download->addresses = json_encode($addresses);
        $download->save();

        try {
            return redirect()->to($download->file->temporary_url);
        } catch (Throwable $e) {
            return '<h1>' . __('frontend/downloads.streaming_problem') . '</h1>';
        }
    }
}
