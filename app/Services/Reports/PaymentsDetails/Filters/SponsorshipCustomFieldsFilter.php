<?php

namespace Ds\Services\Reports\PaymentsDetails\Filters;

use Ds\Domain\Sponsorship\Models\Segment;
use Illuminate\Database\Eloquent\Builder;

class SponsorshipCustomFieldsFilter
{
    public function __invoke(Builder $query): Builder
    {
        if (empty(array_filter((array) request('segment')))) {
            return $query;
        }

        $segmentFilters = array_filter((array) request('segment'));
        Segment::whereKey(array_keys($segmentFilters))
            ->get()
            ->each(function (Segment $segment) use ($query, $segmentFilters) {
                $filteredSegment = $segmentFilters[$segment->id];
                if (in_array($segment->type, ['text', 'date'])) {
                    return
                        $query->whereHas('sponsorship', function (Builder $query) use ($filteredSegment) {
                            $query->whereHas('allSegments', function (Builder $query) use ($filteredSegment) {
                                $query->withoutGlobalScope('sequence')->where('value', 'LIKE', '%' . $filteredSegment . '%');
                            });
                        });
                }

                $query->whereHas('sponsorship', function (Builder $query) use ($filteredSegment) {
                    $query->whereHas('allSegments', function (Builder $query) use ($filteredSegment) {
                        $query->withoutGlobalScope('sequence')->where('segment_item_id', $filteredSegment);
                    });
                });
            });

        return $query;
    }
}
