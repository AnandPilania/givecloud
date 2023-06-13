<?php

namespace Tests\Unit\Helpers;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleAnalyticsEventTest extends TestCase
{
    public function testSendGoogleAnalyticsEvent()
    {
        $this->submitGoogleAnalyticsEvent(Http::fixture(
            'google-analytics/transaction-event.gif',
            Response::HTTP_OK,
            ['Content-Type' => 'image/gif']
        ));

        Http::assertSent(function (Request $request) {
            return $request->toPsrRequest()->getUri()->getPath() === '/collect';
        });
    }

    public function testSendGoogleAnalyticsDebugEvent()
    {
        $this->submitGoogleAnalyticsEvent(Http::fixture('google-analytics/transaction-event.json'), true);

        Http::assertSent(function (Request $request) {
            return $request->toPsrRequest()->getUri()->getPath() === '/debug/collect';
        });
    }

    private function submitGoogleAnalyticsEvent(PromiseInterface $response, bool $debug = false): void
    {
        sys_set(['webStatsPropertyId' => 'UA-XXXXXXXX-XX']);

        Http::fake(['google-analytics.com/*' => $response]);

        google_analytics_event([
            't' => 'transaction',
            'ti' => 'AM91UYE5CHU',
            'ta' => 'DS',
            'tr' => 24.53,
            'ts' => 5.65,
            'tt' => 5.87,
            'cu' => sys_get('dpo_currency'),
        ], $debug);
    }
}
