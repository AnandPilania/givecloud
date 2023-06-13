<?php

namespace Ds\Common\Infusionsoft;

use Infusionsoft\Http\HttpException as InfusionsoftHttpException;

class HttpException extends InfusionsoftHttpException
{
    /** @var array */
    protected $logs = [];

    /**
     * Get the logs.
     *
     * @return array
     */
    public function getLogs(): array
    {
        return $this->logs;
    }

    /**
     * Set the logs.
     *
     * @param array $logs
     * @return static
     */
    public function setLogs(array $logs)
    {
        $this->logs = $logs;

        return $this;
    }
}
