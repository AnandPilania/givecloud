<?php

namespace Ds\Domain\Salesforce\Services;

use Ds\Domain\Salesforce\Models\Transaction;
use Ds\Enums\ExternalReference\ExternalReferenceType;
use Ds\Models\Transaction as EloquentTransaction;

class SalesforceTransactionService extends SalesforceSyncService
{
    protected string $object = Transaction::class;

    protected string $localObject = EloquentTransaction::class;

    protected string $externalType = ExternalReferenceType::TXN;
}
