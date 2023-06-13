<?php

namespace Ds\Http\Controllers\API\Settings;

use Ds\Domain\HotGlue\HotGlue;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class HotGlueSettingsController
{
    public function connect(): JsonResponse
    {
        if ($this->setTarget()) {
            return response()->json();
        }

        return response()->json([], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public function disconnect(): JsonResponse
    {
        if ($this->setTarget(false)) {
            return response()->json();
        }

        return response()->json([], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    protected function setTarget($enable = true): bool
    {
        $name = request()->get('target');

        $target = app(HotGlue::class)->target($name);

        sys_set('hotglue_' . $target->name . '_linked', $enable);

        Cache::forget(implode(':', ['hotglue', $target->name, 'connected']));

        return true;
    }
}
