<?php

namespace Ds\Http\Controllers\Frontend;

use Ds\Models\Pledge;

class PledgesController extends Controller
{
    public function thankYou($number)
    {
        $pledge = Pledge::findOrFail($number);

        return $this->renderTemplate('thank-you', [
            'pledge' => $pledge,
        ]);
    }
}
