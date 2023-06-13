<?php

namespace Ds\Models;

use Ds\Domain\Shared\DateTimePeriodable;
use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Illuminate\Database\Eloquent\Factories\HasFactory;
use Ds\Models\Observers\GroupAccountObserver;
use Ds\Models\Traits\HasNullableDateTimePeriod;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroupAccount extends AbstractGroupAccount implements DateTimePeriodable
{
    use HasNullableDateTimePeriod;
    use HasFactory;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        // saving observer
        self::saving(function ($groupAccount) {
            // prevent end dates earlier than start dates
            if ($groupAccount->end_date && $groupAccount->start_date && $groupAccount->end_date->lt($groupAccount->start_date)) {
                throw new MessageException('Group or membership end date (' . $groupAccount->end_date->format('M j, Y') . ') is earlier than its start date (' . $groupAccount->start_date->format('M j, Y') . ').');
            }
        });

        self::observe(GroupAccountObserver::class);
    }

    public function groupAccountTimespan(): BelongsTo
    {
        return $this->belongsTo(GroupAccountTimespan::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    /**
     * Reload the current model instance with fresh attributes from the database.
     *
     * @return $this
     */
    public function refresh()
    {
        parent::refresh();

        // the groupAccountTimespan relation needs to be manually loaded because
        // it is skipped during the refresh due to GroupAccountTimespan extending Pivot
        if ($this->relationLoaded('groupAccountTimespan')) {
            $this->load('groupAccountTimespan');
        }

        return $this;
    }
}
