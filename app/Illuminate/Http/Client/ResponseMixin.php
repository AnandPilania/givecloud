<?php

namespace Ds\Illuminate\Http\Client;

use GuzzleHttp\Psr7\Message;
use GuzzleHttp\TransferStats;
use Psr\Http\Message\RequestInterface;
use SimpleXMLElement;
use Spatie\Url\QueryParameterBag;

/**
 * @property \GuzzleHttp\TransferStats $transferStats
 *
 * @mixin \Illuminate\Http\Client\Response
 * @mixin \Psr\Http\Message\ResponseInterface
 */
class ResponseMixin
{
    public function getRequest()
    {
        return function (): ?RequestInterface {
            return optional($this->transferStats)->getRequest();
        };
    }

    public function getTransferStats()
    {
        return function (): ?TransferStats {
            return $this->transferStats;
        };
    }

    public function getDebugInfo()
    {
        return function (): array {
            /** @var \Psr\Http\Message\RequestInterface */
            $request = $this->getRequest();

            $data = [
                'General' => [
                    'Request URL' => (string) $request->getUri(),
                    'Request Method' => (string) $request->getMethod(),
                    'Status Code' => (int) $this->getStatusCode(),
                ],
            ];

            $data['Request Headers'] = array_map(function ($values) {
                return implode(', ', $values);
            }, $request->getHeaders());

            $data['Response Headers'] = array_map(function ($values) {
                return implode(', ', $values);
            }, $this->getHeaders());

            if ($query = $request->getUri()->getQuery()) {
                $data['Query String Parameters'] = QueryParameterBag::fromString($query)->all();
            }

            if ($body = (string) $request->getBody()) {
                $data['Form Data'] = $body;
            }

            if ($summary = $this->getBodySummary()) {
                $data['Preview'] = $summary;
            }

            return $data;
        };
    }

    public function getRequestDebugString()
    {
        return function (): string {
            return $this->getRequestStartLine() . "\n" . $this->getRequestHeadersAsString();
        };
    }

    public function getResponseDebugString()
    {
        return function (): string {
            return collect([
                $this->getStartLine(),
                count($this->getHeaders()) ? $this->getHeadersAsString() . "\n" : null,
                $this->getBodySummary(140), // @phpstan-ignore-line
            ])->filter()
                ->implode("\n");
        };
    }

    public function getStartLine()
    {
        return function (): string {
            return sprintf(
                'HTTP/%s %d %s',
                $this->getProtocolVersion(),
                $this->getStatusCode(),
                $this->getReasonPhrase()
            );
        };
    }

    public function getRequestStartLine()
    {
        return function (): string {
            /** @var \Psr\Http\Message\RequestInterface */
            $request = $this->getRequest();

            return sprintf(
                '%s %s HTTP/%s',
                $request->getMethod(),
                $request->getUri(),
                $this->getProtocolVersion()
            );
        };
    }

    public function getHeadersAsString()
    {
        return function (): string {
            return collect($this->getHeaders())
                ->map(function ($values, $name) {
                    return "{$name}: " . implode(', ', $values);
                })->implode("\n");
        };
    }

    public function getRequestHeadersAsString()
    {
        return function (): string {
            /** @var \Psr\Http\Message\RequestInterface */
            $request = $this->getRequest();

            return collect($request->getHeaders())
                ->map(function ($values, $name) {
                    return "{$name}: " . implode(', ', $values);
                })->implode("\n");
        };
    }

    public function getBodySummary()
    {
        return function (int $truncateAt = 120): ?string {
            return Message::bodySummary($this->toPsrResponse(), $truncateAt);
        };
    }

    public function xml()
    {
        return function (array $config = []): ?SimpleXMLElement {
            return app(XmlParser::class)((string) $this->getBody(), $config);
        };
    }
}
