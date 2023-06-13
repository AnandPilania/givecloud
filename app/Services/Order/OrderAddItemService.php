<?php

namespace Ds\Services\Order;

use Ds\Models\Order;
use Ds\Models\OrderItem;
use Ds\Models\Variant;

class OrderAddItemService
{
    /** @var \Ds\Services\Order\OrderAddItemCreationService */
    protected $orderAddItemCreationService;

    /** @var \Ds\Services\Order\OrderAddItemLogicValidationService */
    protected $orderAddItemLogicValidationService;

    /** @var \Ds\Services\Order\OrderAddItemRequestValidationService */
    protected $orderAddItemRequestValidationService;

    /** @var \Ds\Models\Variant */
    protected $variantQueryBuilder;

    public function __construct(
        OrderAddItemCreationService $orderAddItemCreationService,
        OrderAddItemLogicValidationService $orderAddItemLogicValidationService,
        OrderAddItemRequestValidationService $orderAddItemRequestValidationService,
        Variant $variantQueryBuilder
    ) {
        $this->orderAddItemCreationService = $orderAddItemCreationService;
        $this->orderAddItemLogicValidationService = $orderAddItemLogicValidationService;
        $this->orderAddItemRequestValidationService = $orderAddItemRequestValidationService;
        $this->variantQueryBuilder = $variantQueryBuilder;
    }

    public function store(array $data, Order $order): OrderItem
    {
        $this->orderAddItemLogicValidationService->checkIfOrderPaidAlready($order);

        $request = $this->orderAddItemRequestValidationService->validate($data);

        $variant = $this->findVariant($request->variant_id);

        $this->orderAddItemLogicValidationService->checkDonationReachesMinimumAmount($order, $variant, $request->amt);

        // eager load the order items and variants
        // for use during validation of the new order item
        $order->load('items.variant.product');

        // validate every single variant being added to the cart
        $this->orderAddItemLogicValidationService->validateAllVariants(
            $variant->linkedVariants->merge([$variant]),
            $order->items,
            $request->qty
        );

        if (! $order->exists) {
            $order->save();
        }

        $orderItem = $this->orderAddItemCreationService->create($order, $variant, $request);

        // refresh items
        $order->load('items');
        $order->calculate();
        $order->reapplyPromos();

        // doesn't really make sense to be doing this here
        // this should happen when a new Order is created and also
        // done for the cart when a member signs in or out
        if ($order->member_id) {
            $order->member->applyMembershipPromocodes($order);
        }

        return $orderItem;
    }

    protected function findVariant(int $variantId): ?Variant
    {
        return $this->variantQueryBuilder
            ->with(['product', 'linkedVariants', 'linkedVariants.product'])
            ->find($variantId);
    }
}
