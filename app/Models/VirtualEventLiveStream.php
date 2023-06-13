<?php

namespace Ds\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VirtualEventLiveStream extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    public $casts = [
        'stream_key' => 'encrypted',
    ];

    /**
     * Attribute: Video ID
     *
     * @return string
     */
    public function getVideoIdAttribute()
    {
        return $this->status === 'complete' ? $this->playback_video_id : $this->streaming_video_id;
    }

    public function virtualEvent(): BelongsTo
    {
        return $this->belongsTo(VirtualEvent::class);
    }
}
