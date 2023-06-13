<?php

namespace Ds\Domain\HotGlue;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class HotGlueClient
{
    const BASE_URL = 'https://client-api.hotglue.xyz/%s/';

    public function client(): PendingRequest
    {
        $url = sprintf(
            self::BASE_URL,
            config('services.hotglue.env_id')
        );

        return Http::withHeaders([
            'x-api-key' => config('services.hotglue.private_key'),
        ])->baseUrl($url)
            ->asJson();
    }
}
