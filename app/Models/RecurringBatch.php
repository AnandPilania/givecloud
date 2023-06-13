<?php

namespace Ds\Models;

use Ds\Illuminate\Database\Eloquent\Factories\HasFactory;
use Ds\Illuminate\Database\Eloquent\Model;
use Ds\Services\RecurringBatchService;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RecurringBatch extends Model
{
    use HasFactory;

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'batched_on' => 'date',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
        'elapsed_time' => 'int',
        'max_simultaneous' => 'int',
        'accounts_count' => 'int',
        'accounts_processed' => 'int',
        'transactions_approved' => 'int',
        'transactions_declined' => 'int',
    ];

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public static function start(int $accountCount, int $maxSimultaneous): self
    {
        return app(RecurringBatchService::class)->start($accountCount, $maxSimultaneous);
    }

    public function accountProcessed(): bool
    {
        return app(RecurringBatchService::class)->accountProcessed($this);
    }

    public function finish(): bool
    {
        return app(RecurringBatchService::class)->finish($this);
    }

    public function sendSummaryToAccountAdmins(): void
    {
        app(RecurringBatchService::class)->sendSummaryToAccountAdmins($this);
    }
}
