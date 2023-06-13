<?php

namespace Ds\Domain\Salesforce\Services;

use Ds\Domain\Salesforce\Models\Contribution;
use Ds\Enums\ExternalReference\ExternalReferenceType;
use Ds\Models\Order;

class SalesforceContributionService extends SalesforceSyncService
{
    protected string $object = Contribution::class;

    protected string $localObject = Order::class;

    protected string $externalType = ExternalReferenceType::ORDER;
}
