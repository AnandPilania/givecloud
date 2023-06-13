<?php

namespace Ds\Http\Controllers\Frontend\API\Account;

use Ds\Http\Controllers\Frontend\API\Controller;

class TaxReceiptsController extends Controller
{
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTaxReceipts()
    {
        return $this->failure();
    }
}
