<?php

namespace Ds\Domain\Sponsorship\Models;

use Ds\Domain\Sponsorship\Models\Observers\SponsorshipObserver;
use Ds\Domain\Theming\Liquid\Liquidable;
use Ds\Eloquent\Casts\Gender;
use Ds\Eloquent\Casts\Truth;
use Ds\Eloquent\Permissions;
use Ds\Eloquent\SoftDeleteBooleans;
use Ds\Eloquent\Userstamps;
use Ds\Illuminate\Database\Eloquent\AuthoritativeDatabase;
use Ds\Illuminate\Database\Eloquent\Factories\HasFactory;
use Ds\Illuminate\Database\Eloquent\HasAuthoritativeDatabase;
use Ds\Illuminate\Database\Eloquent\Model;
use Ds\Models\Timeline;
use Illuminate\Support\Str;

class Sponsorship extends Model implements AuthoritativeDatabase, Liquidable
{
    use HasAuthoritativeDatabase;
    use HasFactory;
    use Permissions;
    use SoftDeleteBooleans;
    use Userstamps;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'sponsorship';

    /**
     * The attributes that aren't mass assignable.
     *
     * @var string[]|bool
     */
    protected $guarded = [];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'is_deleted' => Truth::class,
        'is_enabled' => Truth::class,
        'is_sponsored' => Truth::class,
        'is_sponsored_auto' => 'boolean',
        'gender' => Gender::class,
        'birth_date' => 'date',
        'enrollment_date' => 'date',
        'created_at' => 'date',
        'updated_at' => 'date',
    ];

    /**
     * Default attributes
     *
     * @var array
     */
    protected $attributes = [
        'is_deleted' => 0,
        'is_enabled' => 0,
        'is_sponsored' => 0,
        'is_sponsored_auto' => 1,
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'age',
        'fields',
        'full_name',
        'image_thumbnail',
        'is_image_valid',
        'url',
        'payment_options',
        'payment_options_recurring',
        'payment_options_one_time',
    ];

    /** @var array */
    protected $allSegmentsMap;

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        self::observe(new SponsorshipObserver);
    }

    /**
     * Relationship: Public Segments
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function segments()
    {
        return $this->allSegments()->publicSegments();
    }

    /**
     * Relationship: Private Segments
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function privateSegments()
    {
        return $this->allSegments()->privateSegments();
    }

    /**
     * Relationship: All Segments
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function allSegments()
    {
        return $this->belongsToMany(Segment::class, SponsorshipSegment::table(), 'sponsorship_id', 'segment_id')
            ->withPivot('value', 'segment_item_id')
            ->leftJoin(
                SegmentItem::table(),
                SegmentItem::table() . '.id',
                '=',
                SponsorshipSegment::table() . '.segment_item_id'
            )->select([
                Segment::table() . '.*',
                SegmentItem::table() . '.name as pivot_segment_item_name',
            ]);
    }

    /**
     * Relationship: Payment Option Group
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function paymentOptionGroups()
    {
        return $this->belongsToMany(PaymentOptionGroup::class, 'sponsorship_payment_option_groups');
    }

    /**
     * Relationship: Timeline
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function timeline()
    {
        $table = Timeline::table();

        return $this->hasMany(Timeline::class, 'parent_id')
            ->where("$table.parent_type", '=', 'sponsorship');
    }

    /**
     * Relationship: Public Timeline
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function publicTimeline()
    {
        return $this->timeline()
            ->public()
            ->orderBy('posted_on', 'desc')
            ->orderBy('id', 'desc');
    }

    /**
     * Relationship: Sponsors
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sponsors()
    {
        return $this->hasMany(Sponsor::class);
    }

    /**
     * Relationship: Sponsors
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function activeSponsors()
    {
        return $this->hasMany(Sponsor::class)->active();
    }

    /**
     * Relationship: Featured Image
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function featuredImage()
    {
        return $this->belongsTo(SponsorshipMedia::class, 'media_id');
    }

    /**
     * Relationship: Media
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function media()
    {
        return $this->morphToMany(SponsorshipMedia::class, 'mediable');
    }

    /**
     * Attribute Mutator: Thumbnail URL
     *
     * @return int
     */
    public function getThumbnailUrlAttribute()
    {
        return media_thumbnail($this);
    }

    /*
     * Attribute Mask: months_waiting
     *
     * Uses enrollment_date to determine the months waiting.
     */
    public function getMonthsWaitingAttribute()
    {
        return ($this->enrollment_date && $this->enrollment_date->isPast()) ? $this->enrollment_date->diffInMonths(fromLocal('now')) : 0;
    }

    /**
     * Attribute Mask: payment_option_group
     * !!!! USED IN TEMPLATING !!!!
     *
     * @return \Ds\Domain\Sponsorship\Models\PaymentOptionGroup
     */
    public function getPaymentOptionGroupAttribute()
    {
        return $this->paymentOptionGroups->first();
    }

    /**
     * Attribute Mask: payment_options
     * !!!! USED IN TEMPLATING !!!!
     *
     * @return \Ds\Domain\Sponsorship\Models\PaymentOptionGroup|null
     */
    public function getPaymentOptionsAttribute()
    {
        if ($this->payment_option_group) {
            return $this->payment_option_group->options;
        }

        return null;
    }

    /**
     * Attribute Mask: payment_options_recurring
     * !!!! USED IN TEMPLATING !!!!
     *
     * @return \Ds\Domain\Sponsorship\Models\PaymentOptionGroup|null
     */
    public function getPaymentOptionsRecurringAttribute()
    {
        if ($this->payment_options) {
            return $this->payment_options->filter(function ($option) {
                return $option->is_recurring == true;
            });
        } else {
            return null;
        }
    }

    /**
     * Attribute Mask: payment_options_one_time
     * !!!! USED IN TEMPLATING !!!!
     *
     * @return \Ds\Domain\Sponsorship\Models\PaymentOptionGroup|null
     */
    public function getPaymentOptionsOneTimeAttribute()
    {
        if ($this->payment_options) {
            return $this->payment_options->filter(function ($option) {
                return $option->is_recurring == false;
            });
        } else {
            return null;
        }
    }

    /**
     * Get the age of the sponsorship record.
     *
     * @return string|null
     */
    public function getAgeAttribute()
    {
        if ($this->birth_date) {
            return $this->birth_date->diffInYears();
        }
    }

    /**
     * Attribute mask: display_name
     *
     * Get the full display name of the sponsorship record.
     *
     * @return string
     */
    public function getDisplayNameAttribute()
    {
        return $this->full_name;
    }

    /**
     * Attribute mask: full_name
     *
     * Get the full display name of the sponsorship record.
     * USED IN TEMPLATING
     *
     * @return string
     */
    public function getFullNameAttribute()
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Attribute mask: full_name
     *
     * Get the full display name of the sponsorship record.
     * USED IN TEMPLATING
     *
     * @return string
     */
    public function getFullNameReverseAttribute()
    {
        return (trim($this->last_name) === '' ? '[none]' : $this->last_name) . ', ' .
               (trim($this->first_name) === '' ? '[none]' : $this->first_name);
    }

    /**
     * Attribute mask: is_image_valid
     *
     * USED IN TEMPLATING
     *
     * @return string
     */
    public function getIsImageValidAttribute()
    {
        return $this->media_id;
    }

    /**
     * Attribute mask: image_thumbnail
     *
     * USED IN TEMPLATING
     *
     * @return string
     */
    public function getImageThumbnailAttribute()
    {
        if ($this->is_image_valid) {
            return media_thumbnail($this);
        }

        return '';
    }

    /**
     * Attribute mask: url
     *
     * @return string
     */
    public function getUrlAttribute()
    {
        return "/sponsorship/{$this->id}";
    }

    /**
     * Attribute mask: html_button
     * Returns this sponsorship as an HTML button in JPANEL.
     * Formatting for male vs female.
     *
     * @return string
     */
    public function getHtmlButtonAttribute()
    {
        return '<a href="/jpanel/sponsorship/' . $this->id . '" class="sponsorship-list-item btn btn-' . (($this->gender === 'M') ? 'info' : (($this->gender === 'F') ? 'pink' : 'default')) . ' btn-xs"><i class="fa fa-' . (($this->gender === 'M') ? 'male' : (($this->gender === 'F') ? 'female' : 'user')) . '"></i> ' . $this->full_name . (($this->reference_number) ? ' (' . $this->reference_number . ')' : '') . '</a>';
    }

    /**
     * Attribute mask: fields
     *
     * @return array
     */
    public function getFieldsAttribute()
    {
        $fields = [];

        // Loops over segments and create an array of slugged keys and values
        foreach ($this->segments as $segment) {
            $fields[Str::slug($segment->name, '_')] = $this->segmentValue($segment);
        }

        return $fields;
    }

    /**
     * Update the status of this sponsorship record based on linked sponsors.
     *
     * AUTOSAVE OPTION IS IMPORTANT for handling recurrsion on model observers.
     *
     * @param bool $autosave
     */
    public function updateIsSponsored($autosave = true)
    {
        // only if auto sponsored is selected
        if (! $this->is_sponsored_auto) {
            return;
        }

        // update the value in the databse
        $this->is_sponsored = ($this->sponsor_count >= sys_get('sponsorship_num_sponsors'));

        // save model
        if ($autosave) {
            $this->save();
        }
    }

    /**
     * Scope: active
     *
     * @return string
     */
    public function scopeActive($query)
    {
        return $query->where('is_enabled', 1);
    }

    /**
     * Check to see if this sponorship is linked to a segment item id.
     *
     * @return bool
     */
    public function hasSegmentItem(SegmentItem $item)
    {
        return (bool) $this->allSegments->filter(function ($segment) use ($item) {
            return $segment->pivot->segment_item_id == $item->id;
        })->count();
    }

    /**
     * Get the value of a segment.
     *
     * @param \Ds\Domain\Sponsorship\Models\Segment $segment
     * @return string|null
     */
    public function segmentValue(Segment $segment)
    {
        if (empty($this->allSegmentsMap)) {
            $this->allSegmentsMap = $this->allSegments->keyBy('id')->all();
        }

        return $this->allSegmentsMap[$segment->id]->value ?? null;
    }

    /**
     * Get the value of a public segment by name.
     *
     * @param string $segmentName
     * @return string|null
     */
    public function segmentValueByName($segmentName)
    {
        $segment = $this->segments->where('name', $segmentName)->first();

        return $this->segmentValue($segment);
    }

    /**
     * Update the is_sponsored flag on all sponsorship records.
     *
     * @return void
     */
    public static function updateAllIsSponsored()
    {
        Sponsorship::where('is_sponsored_auto', true)->where('sponsor_count', '>=', sys_get('sponsorship_num_sponsors'))->update(['is_sponsored' => 1]);
        Sponsorship::where('is_sponsored_auto', true)->where('sponsor_count', '<', sys_get('sponsorship_num_sponsors'))->update(['is_sponsored' => 0]);
    }

    /**
     * Liquid representation of model.
     */
    public function toLiquid()
    {
        return \Ds\Domain\Theming\Liquid\Drop::factory($this, 'Sponsee');
    }
}
