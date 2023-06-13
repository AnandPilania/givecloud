<?php

namespace Ds\Domain\Flatfile\Controllers;

use Ds\Domain\Flatfile\Services\Sponsorships;
use Ds\Http\Controllers\Controller;

class TokenController extends Controller
{
    public function sponsorships()
    {
        return response()->json([
            'token' => app(Sponsorships::class)->token(),
        ]);
    }
}
