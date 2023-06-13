<?php

namespace Ds\Http\Controllers\API;

use Ds\Http\Controllers\Controller;
use Ds\Models\UserPageVisit;
use Illuminate\Http\JsonResponse;

class TrackPageVisitController extends Controller
{
    public function __invoke(): JsonResponse
    {
        if (session('user_login_id')) {
            UserPageVisit::create([
                'url' => request('path'),
                'user_id' => user('id'),
                'user_login_id' => session('user_login_id'),
            ]);
        }

        return response()->json(['success' => true]);
    }
}
