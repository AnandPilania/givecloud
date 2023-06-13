<?php

namespace Ds\Models;

use Ds\Domain\Shared\DateTimePeriodableAggregate;
use Ds\Domain\Shared\Services\DateTimePeriodableMerger;
use Ds\Models\Traits\HasNullableDateTimePeriod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Collection;

class GroupAccountTimespan extends AbstractGroupAccount
{
    use HasNullableDateTimePeriod;
    use HasFactory;

    protected $fillable = [
        'account_id',
        'end_date',
        'group_id',
        'start_date',
    ];

    public function groupAccounts(): Builder
    {
        return GroupAccount::query()->where('account_id', $this->account_id)
            ->where('group_id', $this->group_id)
            ->orderBy('start_date');
    }

    public function getGroupAccountsAttribute(): Collection
    {
        return $this->groupAccounts()->get();
    }

    public static function aggregate(int $groupId, int $accountId): Collection
    {
        $periodables = GroupAccount::query()
            ->where('group_id', $groupId)
            ->where('account_id', $accountId)
            ->get();

        GroupAccountTimespan::query()
            ->where('group_id', $groupId)
            ->where('account_id', $accountId)
            ->delete();

        return app(DateTimePeriodableMerger::class)->merge($periodables)
            ->getAggregates()
            ->map(function (DateTimePeriodableAggregate $aggregate) use ($groupId, $accountId) {
                $timespan = GroupAccountTimespan::create([
                    'group_id' => $groupId,
                    'account_id' => $accountId,
                    'start_date' => $aggregate->getStart(),
                    'end_date' => $aggregate->getEnd(),
                ]);

                GroupAccount::query()
                    ->whereIn('id', $aggregate->getPeriodables()->map->getKey())
                    ->update(['group_account_timespan_id' => $timespan->getKey()]);

                return $timespan;
            });
    }
}
