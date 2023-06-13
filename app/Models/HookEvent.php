<?php

namespace Ds\Models;

use Ds\Illuminate\Database\Eloquent\Factories\HasFactory;
use Ds\Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Collection;

class HookEvent extends Model
{
    use HasFactory;

    /**
     * List of allowed events.
     *
     * @var array
     */
    public const EVENTS = [
        'contribution_paid' => true,
        'contributions_paid' => true,
        'contribution_refunded' => true,
        'supporter_created' => true,
        'supporter_updated' => true,
        'order_completed' => false, // disabled: legacy
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'name' => 'string',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    public function hook(): BelongsTo
    {
        return $this->belongsTo(Hook::class);
    }

    public static function getEnabledEvents(): Collection
    {
        return (new Collection(HookEvent::EVENTS))
            ->filter(function ($enabled) {
                return $enabled;
            })->keys();
    }
}
