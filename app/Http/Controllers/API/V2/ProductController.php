<?php

namespace Ds\Http\Controllers\API\V2;

use Ds\Http\Queries\ProductsQuery;
use Ds\Http\Resources\ProductResource;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProductController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        user()->canOrRedirect(['product.view']);
        $query = (new ProductsQuery)->paginate();

        return ProductResource::collection($query);
    }

    public function show(string $productId): ProductResource
    {
        user()->canOrRedirect(['product.view']);
        $query = (new ProductsQuery)->hashid($productId)->first();

        return ProductResource::make($query);
    }
}
