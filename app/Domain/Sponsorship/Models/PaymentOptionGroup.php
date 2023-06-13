<?php

namespace Ds\Domain\Sponsorship\Models;

use Ds\Eloquent\SoftDeleteBooleans;
use Ds\Eloquent\Userstamps;
use Ds\Illuminate\Database\Eloquent\Factories\HasFactory;
use Ds\Illuminate\Database\Eloquent\Model;

class PaymentOptionGroup extends Model
{
    use HasFactory;
    use SoftDeleteBooleans;
    use Userstamps;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'payment_option_group';

    /**
     * Relationship: Options
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function options()
    {
        return $this->hasMany(PaymentOption::class, 'group_id');
    }
}
