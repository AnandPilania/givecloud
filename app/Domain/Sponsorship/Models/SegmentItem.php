<?php

namespace Ds\Domain\Sponsorship\Models;

use Ds\Domain\Theming\Liquid\Liquidable;
use Ds\Eloquent\SoftDeleteBooleans;
use Ds\Eloquent\Userstamps;
use Ds\Illuminate\Database\Eloquent\AuthoritativeDatabase;
use Ds\Illuminate\Database\Eloquent\Factories\HasFactory;
use Ds\Illuminate\Database\Eloquent\HasAuthoritativeDatabase;
use Ds\Illuminate\Database\Eloquent\Model;

class SegmentItem extends Model implements AuthoritativeDatabase, Liquidable
{
    use HasAuthoritativeDatabase;
    use HasFactory;
    use SoftDeleteBooleans;
    use Userstamps;

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_deleted' => 'boolean',
    ];

    /**
     * Relationship: Segment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function segment()
    {
        return $this->belongsTo(Segment::class);
    }

    /**
     * Liquid representation of model.
     */
    public function toLiquid()
    {
        return \Ds\Domain\Theming\Liquid\Drop::factory($this, 'SponseeFieldOption');
    }
}
