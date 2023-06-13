<?php

namespace Tests\Unit\Illuminate\Http\Client;

use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PendingRequestMixinTest extends TestCase
{
    public function testBuildClientForDirectUsageContainsLaravelDataOption(): void
    {
        /** @var \GuzzleHttp\Client */
        $client = Http::buildClientForDirectUsage();

        $this->assertArrayHasKey('laravel_data', $client->getConfig());
    }
}
