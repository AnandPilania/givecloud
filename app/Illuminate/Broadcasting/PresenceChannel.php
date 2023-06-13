<?php

namespace Ds\Illuminate\Broadcasting;

use Illuminate\Broadcasting\PresenceChannel as BroadcastingChannel;

class PresenceChannel extends BroadcastingChannel
{
    /**
     * Create a new channel instance.
     *
     * @param string $name
     * @return void
     */
    public function __construct($name)
    {
        parent::__construct(sys_get('ds_account_name') . '.' . $name);
    }
}
