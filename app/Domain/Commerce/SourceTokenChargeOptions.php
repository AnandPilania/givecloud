<?php

namespace Ds\Domain\Commerce;

use Ds\Common\DataAccess;
use Ds\Domain\Commerce\Enums\CredentialOnFileInitiatedBy;

/**
 * @property float $dccAmount
 * @property \Ds\Models\Order|null $contribution
 * @property string $initiatedBy
 * @property bool $recurring
 */
class SourceTokenChargeOptions extends DataAccess
{
    /** @var array */
    protected $attributes = [
        'dccAmount' => 0,
        'initiatedBy' => CredentialOnFileInitiatedBy::CUSTOMER,
        'recurring' => false,
    ];
}
