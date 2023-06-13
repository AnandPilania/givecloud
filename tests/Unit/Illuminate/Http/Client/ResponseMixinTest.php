<?php

namespace Tests\Unit\Illuminate\Http\Client;

use GuzzleHttp\TransferStats;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Psr\Http\Message\RequestInterface;
use Tests\TestCase;

class ResponseMixinTest extends TestCase
{
    public function testGettingTransferStats(): void
    {
        $res = $this->getHttpClientResponse();

        $this->assertInstanceOf(TransferStats::class, $res->getTransferStats());
    }

    public function testGettingRequest(): void
    {
        $res = $this->getHttpClientResponse();

        $this->assertInstanceOf(RequestInterface::class, $res->getRequest());
    }

    public function testGettingDebugInfo(): void
    {
        $res = $this->getHttpClientResponse();

        $debugInfo = $res->getDebugInfo();

        $this->assertIsArray($debugInfo);
        $this->assertSame((string) $res->effectiveUri(), $debugInfo['General']['Request URL'] ?? null);
    }

    public function testGettingRequestDebugString(): void
    {
        $res = $this->getHttpClientResponse();

        $debugString = $res->getRequestDebugString();

        $this->assertMatchesRegularExpression('#^GET .* HTTP/\d\.\d\n#m', $debugString);
    }

    public function testGettingResponseDebugString(): void
    {
        $res = $this->getHttpClientResponse();

        $debugString = $res->getResponseDebugString();

        $this->assertMatchesRegularExpression('#^HTTP/\d\.\d #', $debugString);
    }

    private function getHttpClientResponse(): Response
    {
        Http::fake([
            'example.com/*' => Http::response('Hello world'),
        ]);

        return Http::get('https://example.com/movies.json');
    }
}
