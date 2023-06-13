<?php

namespace Ds\Domain\Salesforce\Services;

use Ds\Domain\Salesforce\Models\ContributionPayment;
use Ds\Illuminate\Database\Eloquent\Relations\Pivot;

class SalesforceContributionPaymentService extends SalesforceSyncService
{
    protected string $object = ContributionPayment::class;

    protected string $localObject = Pivot::class;
}
