<?php

namespace Ds\Models;

use Ds\Illuminate\Database\Eloquent\Auditable;
use Ds\Illuminate\Database\Eloquent\HasAuditing;
use Ds\Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VariantFile extends Model implements Auditable
{
    use HasAuditing;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'productinventoryfiles';

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
        'download_limit' => 'integer',
        'address_limit' => 'integer',
        'expiry_time' => 'integer',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'filename',
        'type',
    ];

    public function variant(): BelongsTo
    {
        return $this->belongsTo(Variant::class, 'inventoryid');
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class, 'fileid');
    }

    /**
     * Mutator: filename
     *
     * @return string
     */
    public function getFilenameAttribute()
    {
        return ($this->file) ? $this->file->filename : '';
    }

    /**
     * Mutator: type
     *
     * @return string
     */
    public function getTypeAttribute()
    {
        return (! empty($this->external_resource_uri)) ? 'external' : 'file';
    }
}
