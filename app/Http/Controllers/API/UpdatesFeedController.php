<?php

namespace Ds\Http\Controllers\API;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UpdatesFeedController extends Controller
{
    public function __invoke(Authenticatable $user, Request $request): JsonResponse
    {
        $user->last_opened_updates_feed_at = toUtc($request->input('last_opened_updates_feed_at'));

        return response()->json(
            null,
            $user->save()
                ? Response::HTTP_NO_CONTENT
                : Response::HTTP_UNPROCESSABLE_ENTITY
        );
    }
}
