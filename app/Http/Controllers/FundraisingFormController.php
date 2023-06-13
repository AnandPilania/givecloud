<?php

namespace Ds\Http\Controllers;

use Ds\Exports\FundraisingForms\PerformanceSummaryExport;
use Ds\Models\Product;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class FundraisingFormController extends Controller
{
    public function exportPerformanceSummary(string $donationForm): BinaryFileResponse
    {
        $product = Product::query()
            ->donationForms()
            ->hashid($donationForm)
            ->firstOrFail();

        return Excel::download(new PerformanceSummaryExport($product), 'performance-summary.csv');
    }
}
