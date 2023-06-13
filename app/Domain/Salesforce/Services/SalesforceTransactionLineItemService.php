<?php

namespace Ds\Domain\Salesforce\Services;

use Ds\Domain\Salesforce\Models\TransactionLineItem;
use Ds\Models\Transaction;

class SalesforceTransactionLineItemService extends SalesforceSyncService
{
    protected string $object = TransactionLineItem::class;

    protected string $localObject = Transaction::class;

    protected string $externalType = '';
}
