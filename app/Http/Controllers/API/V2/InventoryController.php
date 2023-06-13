<?php

namespace Ds\Http\Controllers\API\V2;

use Ds\Enums\StockAdjustmentState;
use Ds\Enums\StockAdjustmentType;
use Ds\Http\Requests\API\V2\InventoryStoreFormRequest;
use Ds\Models\StockAdjustment;
use Ds\Models\Variant;
use Illuminate\Http\Response;

class InventoryController extends Controller
{
    public function store(InventoryStoreFormRequest $request, string $variantHashId): Response
    {
        $variant = Variant::find($variantHashId);

        $adjustment = new StockAdjustment;
        $adjustment->type = StockAdjustmentType::PHYSICAL_COUNT;
        $adjustment->variant_id = $variant->getKey();
        $adjustment->state = StockAdjustmentState::IN_STOCK;
        $adjustment->quantity = $request->quantity;
        $adjustment->occurred_at = now();
        $adjustment->user_id = user('id');
        $adjustment->save();

        return response()->noContent();
    }
}
