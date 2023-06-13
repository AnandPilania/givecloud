<?php

namespace Tests\Unit\Common\DonorPerfect;

use Ds\Common\Activitron\Activitron;
use Ds\Common\DonorPerfect\Client;
use Ds\Common\DonorPerfect\RequestException;
use Ds\Illuminate\Http\Client\XmlParseException;
use Ds\Illuminate\Http\Client\XmlParser;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\HttpClientException;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Request as HttpRequest;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Mockery;
use Tests\TestCase;

class ClientTest extends TestCase
{
    public function testCanMakeRequest(): void
    {
        Http::fake([
            'donorperfect.net/*' => Http::fixture('donorperfect/donor.xml'),
        ]);

        $results = $this->app->make(Client::class)->request("select * from [dp] where [donor_id] = '1981'");

        $this->assertInstanceOf(Collection::class, $results);
        $this->assertCount(1, $results);
        $this->assertSame('1981', $results[0]->donor_id);
    }

    /**
     * @dataProvider partnerTagQueryStringPositionProvider
     */
    public function testPartnerTagComesAfterApikeyInQueryString(string $requestMethod): void
    {
        sys_set([
            'dpo_api_key' => 'api_key',
            'dp_request_method' => $requestMethod,
        ]);

        Http::fake([
            'donorperfect.net/*' => Http::fixture('donorperfect/donor.xml'),
        ]);

        $this->app->make(Client::class)->request("select * from [dp] where [donor_id] = '1981'");

        Http::assertSent(function (Request $request) {
            $query = $request->toPsrRequest()->getUri()->getQuery();

            return preg_match('/apikey=\w+&%40tag=/', $query) === 1;
        });
    }

    /**
     * @dataProvider partnerTagQueryStringPositionProvider
     */
    public function testPartnerTagComesAfterPassInQueryString(string $requestMethod): void
    {
        sys_set([
            'dpo_api_key' => null,
            'dpo_user' => 'user',
            'dpo_pass' => 'pass',
            'dp_request_method' => $requestMethod,
        ]);

        Http::fake([
            'donorperfect.net/*' => Http::fixture('donorperfect/donor.xml'),
        ]);

        $this->app->make(Client::class)->request("select * from [dp] where [donor_id] = '1981'");

        Http::assertSent(function (Request $request) {
            $query = $request->toPsrRequest()->getUri()->getQuery();

            return preg_match('/pass=\w+&%40tag=/', $query) === 1;
        });
    }

    public function partnerTagQueryStringPositionProvider(): array
    {
        return [
            [HttpRequest::METHOD_GET],
            [HttpRequest::METHOD_POST],
        ];
    }

    public function testCanTrackServerErrors(): void
    {
        Http::fake([
            'donorperfect.net/*' => Http::fixture('donorperfect/donor.xml', HttpResponse::HTTP_BAD_REQUEST),
        ]);

        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('Network error occurred');

        $this->app->make(Client::class)->request("select * from [dp] where [donor_id] = '1981'");
    }

    public function testCanTrackNetworkErrors(): void
    {
        Http::fake(function () {
            throw new RequestException('A terrible error has occurred', new Response(Http::response()->wait()));
        });

        $this->expectException(HttpClientException::class);
        $this->expectExceptionMessage('Network error occurred');

        $this->app->make(Client::class)->request("select * from [dp] where [donor_id] = '1981'");
    }

    public function testCanTrackTimeouts(): void
    {
        Http::fake(function () {
            throw new ConnectionException;
        });

        $this->expectException(HttpClientException::class);
        $this->expectExceptionMessage('Network error occurred');

        $this->instance('activitron', Mockery::mock(Activitron::class, function ($mock) {
            $mock->shouldReceive('increment')->once()->with('Site.dpo.request.timeouts');
            $mock->shouldReceive('increment')->once()->with('Site.dpo.request.failure');
            $mock->shouldReceive('endTiming')->once()->with('Site.dpo.request.time');
        })->makePartial());

        $this->app->make(Client::class)->request("select * from [dp] where [donor_id] = '1981'");
    }

    public function testCanHandleBadXmlInResponse(): void
    {
        Http::fake([
            'donorperfect.net/*' => Http::fixture('donorperfect/donor.xml'),
        ]);

        $this->partialMock(XmlParser::class, function ($mock) {
            $mock->shouldReceive('__invoke')->once()->andThrow(new XmlParseException('Whoops'));
        });

        $this->expectException(RequestException::class);
        $this->expectExceptionMessage('Unable to parse the response body as XML');

        $this->app->make(Client::class)->request("select * from [dp] where [donor_id] = '1981'");
    }
}
