<?php

namespace Ds\Models;

use Ds\Eloquent\SoftDeleteUserstamp;
use Ds\Eloquent\Userstamps;
use Ds\Illuminate\Database\Eloquent\Factories\HasFactory;
use Ds\Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tax extends Model
{
    use HasFactory;
    use SoftDeletes;
    use SoftDeleteUserstamp;
    use Userstamps;

    /**
     * The name of the "created at" column.
     *
     * @var string
     */
    const CREATED_AT = 'createddatetime';

    /**
     * The name of the "updated at" column.
     *
     * @var string
     */
    const UPDATED_AT = 'modifieddatetime';

    /**
     * The name of the "created by" column.
     *
     * @var string
     */
    const CREATED_BY = 'createdbyuserid';

    /**
     * The name of the "updated by" column.
     *
     * @var string
     */
    const UPDATED_BY = 'modifiedbyuserid';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'producttax';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = ['rate' => 'double'];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'producttaxproduct', 'taxid', 'productid');
    }

    public function regions(): BelongsToMany
    {
        return $this->belongsToMany(Region::class, 'producttaxregion', 'taxid', 'regionid');
    }
}
