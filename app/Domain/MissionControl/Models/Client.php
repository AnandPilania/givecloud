<?php

namespace Ds\Domain\MissionControl\Models;

use Ds\Common\DataAccess;

class Client extends DataAccess
{
    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'ordered_on' => 'datetime',
    ];

    /**
     * Attribute Accessor: Is Development
     *
     * @return bool
     */
    public function getIsDevelopmentAttribute()
    {
        return $this->status === 'DEV';
    }

    /**
     * Attribute Accessor: Full Address
     *
     * @return string
     */
    public function getFullAddressAttribute()
    {
        return address_format(
            $this->address1,
            $this->address2,
            $this->city,
            $this->province,
            $this->postal_code,
            $this->country
        );
    }
}
