<?php

namespace Ds\Http\Controllers\API\Settings;

use Ds\Http\Controllers\API\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    private $storableMetadata = [
        'show_fundraising_pixel_instructions' => 'boolean',
    ];

    public function store(Request $request): array
    {
        $data = collect($request->input('metadata', []))
            ->filter(fn ($value, $key) => $this->storableMetadata[$key] ?? false)
            ->mapWithKeys(function ($value, $key) {
                return ["{$this->storableMetadata[$key]}:$key" => $value];
            })->all();

        if (count($data)) {
            user()->metadata($data);
            user()->save();
        }

        return ['success' => true];
    }
}
