<?php

namespace Ds\Domain\Messenger\Models;

use Ds\Illuminate\Database\Eloquent\Factories\HasFactory;
use Ds\Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Propaganistas\LaravelPhone\PhoneNumber;

class ConversationRecipient extends Model
{
    use HasFactory;
    use SoftDeletes;

    /**
     * Attributes hidden from serialization.
     *
     * @var array
     */
    protected $hidden = [
        'resource_type',
        'nexmo_msisdn',
        'twilio_sid',
        'created_at',
        'updated_at',
        'pivot',
    ];

    public function getIdentifierUsFormattedAttribute(): ?string
    {
        if (! $this->identifier) {
            return null;
        }

        return PhoneNumber::make($this->identifier, 'US')->formatForCountry('US');
    }
}
