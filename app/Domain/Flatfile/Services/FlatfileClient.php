<?php

namespace Ds\Domain\Flatfile\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class FlatfileClient
{
    const REST_API_URL = 'https://api.us.flatfile.io';
    const GRAPHQL_URL = self::REST_API_URL . '/graphql';

    public function jwt(): string
    {
        return Cache::remember('flatfile:jwtToken', now()->addHour(), function () {
            return
                Http::asJson()
                    ->post(self::REST_API_URL . '/auth/access-key/exchange', [
                        'accessKeyId' => config('services.flatfile.access_key'),
                        'secretAccessKey' => config('services.flatfile.private_key'),
                    ])->throw()
                    ->json('accessToken');
        });
    }

    public function getBatch(string $batchId): array
    {
        return
            Http::withHeaders([
                'X-Api-Key' => config('services.flatfile.access_key') . '+' . config('services.flatfile.private_key'),
            ])->asJson()
                ->get(self::REST_API_URL . "/rest/batch/{$batchId}", [
                ])->throw()
                ->json();
    }

    public function getRowsForPage(string $batchId, int $offset = 0): ?array
    {
        return Http::withHeaders([
            'X-Api-Key' => config('services.flatfile.access_key') . '+' . config('services.flatfile.private_key'),
        ])->asJson()
            ->get(self::REST_API_URL . "/rest/batch/{$batchId}/rows", [
                'skip' => $offset,
                'valid' => 1,
                'take' => 50,
            ])->throw()
            ->json();
    }

    public function getSchema(?string $name = null): Collection
    {
        $teamId = config('services.flatfile.team_id');

        $query = 'query {
            getSchemas (teamId:' . $teamId . ') {
                data {
                    id
                    name
                    jsonSchema {
                     schema
                    }
                    createdAt
                    updatedAt
              }
            }
          }';

        return Http::withToken($this->jwt())
            ->withBody(json_encode(['query' => $query], JSON_THROW_ON_ERROR), 'application/json')
            ->post(self::GRAPHQL_URL)
            ->throw()
            ->collect('data.getSchemas.data')
            ->when($name, fn ($collection) => collect($collection->firstWhere('name', $name)));
    }
}
