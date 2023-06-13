<?php

namespace Ds\Models;

use Ds\Eloquent\HasMetadata;
use Ds\Eloquent\Metadatable;
use Ds\Eloquent\Permissions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class VirtualEvent extends Model implements Metadatable
{
    use HasFactory;
    use HasMetadata;
    use Permissions;
    use SoftDeletes;

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'start_date' => 'date',
        'is_chat_enabled' => 'boolean',
        'is_amount_tally_enabled' => 'boolean',
        'is_celebration_enabled' => 'boolean',
        'is_honor_roll_enabled' => 'boolean',
        'is_emoji_reaction_enabled' => 'boolean',
        'celebration_threshold' => 'integer',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(PledgeCampaign::class, 'campaign_id');
    }

    public function productOne(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'tab_one_product_id');
    }

    public function productTwo(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'tab_two_product_id');
    }

    public function productThree(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'tab_three_product_id');
    }

    public function liveStream(): HasOne
    {
        return $this->hasOne(VirtualEventLiveStream::class);
    }

    /**
     * Attribute: Video ID
     *
     * @return string
     */
    public function getLiveStreamVideoIdAttribute()
    {
        if ($this->video_source === 'mux') {
            return $this->liveStream->video_id ?? null;
        }

        return $this->video_id;
    }

    /**
     * Attribute: Video ID
     *
     * @return string
     */
    public function getLiveStreamStatusAttribute()
    {
        if ($this->video_source === 'mux') {
            return $this->liveStream->status;
        }

        return 'active';
    }
}
