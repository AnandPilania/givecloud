<?php

namespace Ds\Http\Controllers\Reports;

use Ds\Http\Controllers\Controller;

class MatureSponsorshipsController extends Controller
{
    public function index()
    {
        return $this->getView('reports/mature-sponsorships', [
            '__menu' => 'reports.mature_sponsorships',
        ]);
    }
}
