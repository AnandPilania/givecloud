<?php

namespace Ds\Domain\Salesforce\Services;

use Ds\Domain\Salesforce\Models\LineItem;
use Ds\Enums\ExternalReference\ExternalReferenceType;
use Ds\Models\OrderItem;

class SalesforceLineItemService extends SalesforceSyncService
{
    protected string $object = LineItem::class;

    protected string $localObject = OrderItem::class;

    protected string $externalType = ExternalReferenceType::ITEM;
}
