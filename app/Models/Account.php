<?php

namespace Ds\Models;

use Database\Factories\MemberFactory;
use Ds\Illuminate\Database\Eloquent\Factories\HasFactory;

class Account extends Member
{
    use HasFactory;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'member';

    /**
     * Get the default foreign key name for the model.
     *
     * @return string
     */
    public function getForeignKey()
    {
        return 'member_id';
    }

    public static function newFactory()
    {
        return MemberFactory::new();
    }
}
