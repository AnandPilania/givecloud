<?php

namespace Ds\Illuminate\Broadcasting;

use Illuminate\Broadcasting\Channel as BroadcastingChannel;

class Channel extends BroadcastingChannel
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
