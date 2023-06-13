<?php

namespace Ds\Domain\Sponsorship\Events;

use Ds\Domain\Sponsorship\Models\Sponsor;
use Ds\Events\Event;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Arr;

class SponsorWasStarted extends Event
{
    use SerializesModels;

    /** array */
    public $options;

    /** @var \Ds\Domain\Sponsorship\Models\Sponsor */
    public $sponsor;

    /**
     * Create a new event instance.
     *
     * @param \Ds\Domain\Sponsorship\Models\Sponsor $sponsor
     * @param array $options
     * @return void
     */
    public function __construct(Sponsor $sponsor, array $options = [])
    {
        $this->sponsor = $sponsor;
        $this->options = $options;
    }

    /**
     * Get an option value.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function option($key, $default = null)
    {
        return Arr::get($this->options, $key, $default);
    }
}
