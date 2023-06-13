<?php

namespace Ds\Http\Controllers\Frontend\API;

use Ds\Domain\Theming\Liquid\Drop;
use Ds\Models\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ProductsController extends Controller
{
    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProduct(Product $product)
    {
        $member = member();

        if (! $product->isenabled) {
            throw (new ModelNotFoundException)->setModel(Product::class);
        }

        $product->load('variants', 'defaultVariant', 'customFields', 'memberships');

        if (feature('membership') && $product->memberships->count()) {
            if ($member) {
                $belongsToGroup = $product->memberships->pluck('id')
                    ->intersect($member->activeGroups()->pluck('id'))
                    ->isNotEmpty();
            } else {
                $belongsToGroup = false;
            }

            if (! $belongsToGroup) {
                throw (new ModelNotFoundException)->setModel(Product::class);
            }
        }

        return $this->success([
            'product' => Drop::factory($product, 'Product'),
        ]);
    }
}
