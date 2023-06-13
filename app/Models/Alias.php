<?php

namespace Ds\Models;

use Ds\Eloquent\Permissions;
use Ds\Illuminate\Database\Eloquent\Model;

class Alias extends Model
{
    use Permissions;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var string[]|bool
     */
    protected $guarded = ['id'];
}
