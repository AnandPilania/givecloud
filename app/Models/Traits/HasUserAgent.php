<?php

namespace Ds\Models\Traits;

use UAParser\Result\Client;

trait HasUserAgent
{
    /** @var \UAParser\Result\Client|null */
    private $userAgentClient;

    public function ua(): Client
    {
        if (empty($this->userAgentClient)) {
            $this->userAgentClient = app('ua')->parse(
                $this->{$this->getUserAgentColumnName()} ?? ''
            );
        }

        return $this->userAgentClient;
    }

    protected function getUserAgentColumnName(): string
    {
        return 'user_agent';
    }
}
