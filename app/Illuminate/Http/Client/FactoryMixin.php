<?php

namespace Ds\Illuminate\Http\Client;

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;

/** @mixin \Illuminate\Http\Client\Factory */
class FactoryMixin
{
    public function fixture()
    {
        return function (string $path, int $status = Response::HTTP_OK, array $headers = []): PromiseInterface {
            $fixturePath = base_path("tests/fixtures/$path");

            if (! File::exists($fixturePath)) {
                throw new FileNotFoundException("No fixture found at [$fixturePath].");
            }

            return Http::response(File::get($fixturePath), $status, $headers);
        };
    }
}
