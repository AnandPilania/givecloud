<?php

namespace Ds\Models;

use Ds\Domain\Theming\Liquid\Liquidable;
use Ds\Eloquent\Permissions;
use Ds\Eloquent\SoftDeleteUserstamp;
use Ds\Eloquent\Userstamps;
use Ds\Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Timeline extends Model implements Liquidable
{
    use SoftDeletes;
    use SoftDeleteUserstamp;
    use Userstamps;
    use Permissions;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'timelines';

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'deleted_at',
        'deleted_by',
        'created_at',
        'created_by',
        'updated_at',
        'updated_by',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'parent_type',
        'parent_id',
        'tag',
        'is_private',
        'headline',
        'message',
        'attachments',
        'attachment_size',
        'data',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at',
        'posted_on',
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'parent_id' => 'integer',
        'is_private' => 'boolean',
        'attachments' => 'array',
        'attachment_size' => 'integer',
        'data' => 'array',
    ];

    /**
     * Attributes to append when doing json/array serialization.
     *
     * @var array
     */
    protected $appends = [
        'icon_class',
        'posted_by_string',
    ];

    public function media(): MorphToMany
    {
        return $this->morphToMany(Media::class, 'mediable');
    }

    /**
     * Scope: Public posts
     *
     * @param \Illuminate\Database\Query\Builder $query
     */
    public function scopePublic($query)
    {
        return $query->where('is_private', false);
    }

    /**
     * Scope: Private posts
     *
     * @param \Illuminate\Database\Query\Builder $query
     */
    public function scopePrivate($query)
    {
        return $query->where('is_private', true);
    }

    /**
     * Return the font awesome icon to use depending on the tag selected.
     *
     * @return string
     */
    public function getIconClassAttribute()
    {
        switch ($this->tag) {
            case 'admin': return 'fa-gear';
            case 'celebration': return 'fa-birthday-cake';
            case 'education': return 'fa-graduation-cap';
            case 'financial': return 'fa-money';
            case 'gift': return 'fa-gift';
            case 'medical': return 'fa-heartbeat';
            case 'milestone': return 'fa-flag';
            case 'travel': return 'fa-map-o';
            case 'weather': return 'fa-cloud';
            case 'family': return 'fa-home';
            case 'letter': return 'fa-file-text';
            case 'update': return 'fa-plus';
            default: return 'fa-pencil';
        }
    }

    /**
     * Return a nicely formatted 'created by' string.
     *
     * @return string|bool
     */
    public function getPostedByStringAttribute()
    {
        if ($this->createdBy) {
            return $this->createdBy->full_name . ' on ' . $this->posted_on->format('M d, Y') . ' (' . $this->posted_on->diffForHumans() . ')';
        }

        return false;
    }

    /**
     * Return available tag options.
     */
    public static function tags(): array
    {
        return [
            'admin' => 'Admin',
            'celebration' => 'Celebration',
            'education' => 'Education',
            'family' => 'Family',
            'financial' => 'Financial',
            'general' => 'General',
            'gift' => 'Gift',
            'letter' => 'Letter',
            'medical' => 'Medical',
            'milestone' => 'Milestone',
            'travel' => 'Travel',
            'update' => 'Update',
            'weather' => 'Weather',
        ];
    }

    /**
     * Liquid representation of model.
     */
    public function toLiquid()
    {
        return \Ds\Domain\Theming\Liquid\Drop::factory($this, 'Timeline');
    }
}
