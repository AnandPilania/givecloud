<?php

namespace Ds\Http\Queries;

use Ds\Models\Transaction;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class TransactionsQuery extends QueryBuilder
{
    public function __construct()
    {
        parent::__construct(Transaction::query()->with([
            'recurringPaymentProfile.order',
            'recurringPaymentProfile.order_item.recurringPaymentProfile',
            'recurringPaymentProfile.order_item.order',
            'recurringPaymentProfile.order_item.sponsorship',
            'recurringPaymentProfile.order_item.lockedItems',
            'recurringPaymentProfile.order_item.variant.product',
            'recurringPaymentProfile.order_item.fields',
            'recurringPaymentProfile.order_item.fundraisingPage.memberOrganizer',
            'recurringPaymentProfile.member.groups',
            'recurringPaymentProfile.member.accountType',
            'recurringPaymentProfile.member.loginAuditLogs',
            'payments',
        ]));

        $this
            ->allowedFilters([
                AllowedFilter::scope('id', 'hashid'),
                AllowedFilter::partial('contribution_number', 'transaction_id'),
                AllowedFilter::scope('ordered_before'),
                AllowedFilter::scope('ordered_after'),
            ])->allowedSorts([''])
            ->allowedIncludes(['']);
    }
}
