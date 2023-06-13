<?php

namespace Ds\Models;

use Ds\Illuminate\Database\Eloquent\Factories\HasFactory;
use Ds\Illuminate\Database\Eloquent\Model;

class MonitoringIncident extends Model
{
    use HasFactory;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var string[]|bool
     */
    protected $guarded = ['id'];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'triggered_at' => 'datetime',
        'recovered_at' => 'datetime',
        'recovered_by' => 'integer',
    ];

    /**
     * Scope: Incident Type
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param string $incidentType
     */
    public function scopeIncidentType($query, $incidentType)
    {
        $query->where('incident_type', $incidentType);
    }

    /**
     * Scope: Open
     *
     * @param \Illuminate\Database\Query\Builder $query
     */
    public function scopeOpen($query)
    {
        $query->whereNotNull('triggered_at');
        $query->whereNull('recovered_at');
    }
}
