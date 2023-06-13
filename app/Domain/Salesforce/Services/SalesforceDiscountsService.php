<?php

namespace Ds\Domain\Salesforce\Services;

use Ds\Domain\Salesforce\Models\Discount;
use Ds\Models\PromoCode;

class SalesforceDiscountsService extends SalesforceSyncService
{
    protected string $object = Discount::class;

    protected string $localObject = PromoCode::class;
}
