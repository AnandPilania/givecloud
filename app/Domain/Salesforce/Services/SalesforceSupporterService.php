<?php

namespace Ds\Domain\Salesforce\Services;

use Ds\Domain\Salesforce\Models\Supporter;
use Ds\Enums\ExternalReference\ExternalReferenceType;
use Ds\Models\Member;

class SalesforceSupporterService extends SalesforceSyncService
{
    protected string $object = Supporter::class;

    protected string $localObject = Member::class;

    protected string $externalType = ExternalReferenceType::SUPPORTER;
}
