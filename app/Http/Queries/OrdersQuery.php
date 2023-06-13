<?php

namespace Ds\Http\Queries;

use Ds\Models\Order;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class OrdersQuery extends QueryBuilder
{
    public function __construct()
    {
        parent::__construct(Order::query()->with([
            'member.accountType',
            'member.groups',
            'promoCodes',
            'items.fields',
            'items.order',
            'items.variant.product',
            'items.lockedItems',
            'items.sponsorship',
            'items.recurringPaymentProfile',
            'items.fundraisingPage.memberOrganizer',
            'payments',
            'shippingMethod',
        ]));

        $this
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::partial('number'),
                AllowedFilter::scope('ordered_before'),
                AllowedFilter::scope('ordered_after'),
                AllowedFilter::scope('updated_before'),
                AllowedFilter::scope('updated_after'),
            ])->allowedSorts([''])
            ->allowedIncludes(['']);
    }
}
