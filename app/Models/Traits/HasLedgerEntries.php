<?php

namespace Ds\Models\Traits;

use Ds\Models\LedgerEntry;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasLedgerEntries
{
    public function ledgerEntries(): MorphMany
    {
        return $this->morphMany(LedgerEntry::class, 'ledgerable');
    }
}
