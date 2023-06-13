<?php

namespace Ds\Http\Controllers\Frontend\API\Account;

use Ds\Http\Controllers\Frontend\API\Controller;

class SponsorshipsController extends Controller
{
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSponsorships()
    {
        return $this->failure();
    }
}
