<?php

namespace Ds\Http\Controllers\Reports;

use Ds\Http\Controllers\Controller;
use Ds\Models\Product;

class OrderController extends Controller
{
    public function index()
    {
        $product = Product::findOrFail(request('id'));

        return redirect()->route('backend.reports.products.index', $product);
    }
}
