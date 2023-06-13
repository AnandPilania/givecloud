<?php

namespace Ds\Domain\Sponsorship\Models;

use Ds\Eloquent\SoftDeleteBooleans;
use Ds\Eloquent\Userstamps;
use Ds\Illuminate\Database\Eloquent\Factories\HasFactory;
use Ds\Illuminate\Database\Eloquent\Model;

class PaymentOption extends Model
{
    use HasFactory;
    use SoftDeleteBooleans;
    use Userstamps;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'payment_option';

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'sequence' => 'integer',
        'amount' => 'double',
        'is_custom' => 'boolean',
        'is_recurring' => 'boolean',
        'recurring_day' => 'integer',
        'recurring_day_of_week' => 'integer',
        'recurring_with_dpo' => 'boolean',
    ];

    /**
     * Attribute to append toArray
     *
     * @var array
     */
    protected $appends = [
        'description',
        'recurring_description',
        'recurring_frequency_short',
    ];

    /**
     * Relationship: Group
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function group()
    {
        return $this->belongsTo(PaymentOptionGroup::class, 'group_id');
    }

    /**
     * Attribute mask: description
     *
     * @return string
     */
    public function getDescriptionAttribute()
    {
        if ($this->is_recurring == 1) {
            return '$' . number_format($this->amount, 2) . '/' . $this->recurring_frequency;
        }

        return '$' . number_format($this->amount, 2) . ' once';
    }

    /**
     * Attribute mask: description
     *
     * @return string|null
     */
    public function getRecurringFrequencyShortAttribute()
    {
        switch ($this->recurring_frequency) {
            case 'weekly':    return 'wk';
            case 'biweekly':  return 'bi-mth';
            case 'monthly':   return 'mth';
            case 'quarterly': return 'qr';
            case 'annually':  return 'yr';
        }
    }

    /**
     * Attribute mask: recurring_description
     *
     * @return string|null
     */
    public function getRecurringDescriptionAttribute()
    {
        if ($this->is_recurring == 1) {
            // set the period based on the recurring_frequency
            switch ($this->recurring_frequency) {
                case 'weekly':    $period = 'Week'; break;
                case 'biweekly':  $period = 'SemiMonth'; break;
                case 'monthly':   $period = 'Month'; break;
                case 'quarterly': $period = 'Quarter'; break;
                case 'biannually': $period = 'SemiYear'; break;
                case 'annually':  $period = 'Year'; break;
            }

            // set the ordinal suffix of the date
            // eg 1st, 2nd, 3rd, 4th....
            if ($this->recurring_frequency == 'monthly' || $this->recurring_frequency == 'quarterly' || $this->recurring_frequency == 'biannually' || $this->recurring_frequency == 'annually') {
                $day = ' on the ' . $this->recurring_day . date('S', mktime(0, 0, 0, 0, $this->recurring_day, 0));
            }

            // set the day of the week
            else {
                $days = [
                    1 => 'Monday',
                    2 => 'Tuesday',
                    3 => 'Wednesday',
                    4 => 'Thursday',
                    5 => 'Friday',
                    6 => 'Saturday',
                    7 => 'Sunday',
                ];

                $day = ' on ' . $days[$this->recurring_day_of_week];
            }

            return '$' . number_format($this->amount, 2) . '/' . $period . $day;
        }
    }
}
