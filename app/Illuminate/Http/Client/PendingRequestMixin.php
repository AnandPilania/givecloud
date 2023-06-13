<?php

namespace Ds\Illuminate\Http\Client;

use GuzzleHttp\Client;
use GuzzleHttp\TransferStats;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Arr;

/**
 * @property array $options
 * @property \GuzzleHttp\TransferStats $transferStats
 *
 * @mixin \Illuminate\Http\Client\PendingRequest
 */
class PendingRequestMixin
{
    public function buildClientForDirectUsage()
    {
        return function (): Client {
            $this->setClient(new Client([
                'handler' => $this->buildHandlerStack(),
                'cookies' => true,
                // the laravel stack added to the Guzzle client doesn't
                // check for the key when processing the stack so if a request
                // is made directly using the client and not via the PendingRequest
                // an Undefined index warning and TypeError are thrown
                'laravel_data' => [],
                'on_stats' => function (TransferStats $transferStats) {
                    $this->setTransferStats($transferStats); // @phpstan-ignore-line
                },
            ]));

            return $this->buildClient();
        };
    }

    public function getHeadersAsString()
    {
        return function (): string {
            return collect($this->options['headers'])
                ->map(function ($values, $name) {
                    return "{$name}: " . implode(', ', Arr::wrap($values));
                })->implode("\n");
        };
    }

    public function setTransferStats()
    {
        return function (TransferStats $transferStats) {
            $this->transferStats = $transferStats;
        };
    }
}
