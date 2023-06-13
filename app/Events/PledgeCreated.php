<?php

namespace Ds\Events;

use Ds\Models\Pledge;
use Illuminate\Queue\SerializesModels;

class PledgeCreated extends Event
{
    use SerializesModels;

    /** @var \Ds\Models\Pledge */
    public $pledge;

    /**
     * Create a new event instance.
     *
     * @param \Ds\Models\Pledge $pledge
     * @return void
     */
    public function __construct(Pledge $pledge)
    {
        $this->pledge = $pledge;
    }
}
