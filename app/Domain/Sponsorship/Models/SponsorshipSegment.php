<?php

namespace Ds\Domain\Sponsorship\Models;

use Ds\Eloquent\Userstamps;
use Ds\Illuminate\Database\Eloquent\AuthoritativeDatabase;
use Ds\Illuminate\Database\Eloquent\HasAuthoritativeDatabase;
use Ds\Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SponsorshipSegment extends Model implements AuthoritativeDatabase
{
    use HasAuthoritativeDatabase;
    use Userstamps;

    /**
     * Relationship: Sponsorship
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function sponsorship()
    {
        return $this->belongsTo('Ds\Domain\Sponsorship\Models\Sponsorship');
    }

    /**
     * Relationship: Segment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function segment()
    {
        return $this->belongsTo('Ds\Domain\Sponsorship\Models\Segment');
    }

    /**
     * Scope: Only sponsorship segment relations where the segment is public.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublic($query)
    {
        $segments = Segment::table();
        $sponsorshipSegments = SponsorshipSegment::table();

        return $query->join($segments, function ($join) use ($segments, $sponsorshipSegments) {
            $join->on("$segments.id", '=', "$sponsorshipSegments.segment_id");
            $join->where("$segments.show_in_detail", '=', DB::raw(1));
        }, null, 'inner');
    }

    /**
     * Relationship: Item
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function item()
    {
        return $this->belongsTo('Ds\Domain\Sponsorship\Models\SegmentItem', 'segment_item_id');
    }

    /**
     * Attribute mask: value
     *
     * We want to eliminate the search for the VALUE.
     * Sometimes the value is stored in sponsorship_segment.value.
     * Other times, its stored in sponsorship_segment.segment_item_id.name.
     * AND depending on the segment type, it may need a bit of default formatting (like an <a> tag)
     *
     * @param string|null $value
     * @return string|null
     */
    public function getValueAttribute($value = null)
    {
        if ($value) {
            return $value;
        }

        if ($this->item) {
            return $this->item->name;
        }
    }
}
