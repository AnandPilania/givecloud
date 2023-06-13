<?php

namespace Ds\Domain\Salesforce\Services;

use Ds\Domain\Salesforce\Models\Payment;

class SalesforcePaymentsService extends SalesforceSyncService
{
    protected string $object = Payment::class;

    protected string $localObject = \Ds\Models\Payment::class;
}
