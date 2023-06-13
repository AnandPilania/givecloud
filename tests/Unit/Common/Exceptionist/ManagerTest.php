<?php

namespace Tests\Unit\Common\Exceptionist;

use Bugsnag\Configuration;
use Bugsnag\Report;
use Closure;
use DomainException;
use Ds\Common\DonorPerfect\QueryException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ManagerTest extends TestCase
{
    /**
     * @dataProvider incluingHttpResponseInMetaDataForExceptionsProvider
     */
    public function testIncluingHttpResponseInMetaDataForExceptions(string $assertion, Closure $createThrowable): void
    {
        $throwable = $createThrowable();
        $report = Report::fromPHPThrowable(new Configuration('api_key'), $throwable);

        $this->app['exceptionist']->includeMetaDataFromThrowable($report, $throwable);

        $this->{$assertion}('http_response', $report->getMetaData());
    }

    public function incluingHttpResponseInMetaDataForExceptionsProvider(): array
    {
        return [
            [
                'assertArrayNotHasKey',
                function () {
                    return new DomainException;
                },
            ],
            [
                'assertArrayHasKey',
                function () {
                    return $this->createRequestException();
                },
            ],
            [
                'assertArrayHasKey',
                function () {
                    return new QueryException('SELECT * FROM [dpgift]', [], $this->createRequestException());
                },
            ],
        ];
    }

    private function createRequestException(): RequestException
    {
        Http::fake(['example.com/*' => Http::response()]);

        return new RequestException(Http::get('https://example.com'));
    }
}
