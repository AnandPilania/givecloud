<?php

namespace Ds\Domain\Flatfile\Jobs;

use Ds\Domain\Flatfile\Services\FlatfileClient;
use Ds\Jobs\Job;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

abstract class ImportJob extends Job implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use SerializesModels;

    protected string $batchId;
    protected array $batchMetaData;
    protected static $schema;

    public function __construct(string $batchId)
    {
        $this->batchId = $batchId;
    }

    abstract protected function importRow(array $row): void;

    public function handle(): void
    {
        $hasMorePages = true;
        $offset = 0;

        $this->batchMetaData = app(FlatfileClient::class)->getBatch($this->batchId);

        while ($hasMorePages) {
            $data = app(FlatfileClient::class)->getRowsForPage($this->batchId, $offset);

            $offset = data_get($data, 'pagination.nextOffset');
            $hasMorePages = (bool) $offset;

            foreach (data_get($data, 'data', []) as $row) {
                $this->importRow(data_get($row, 'mapped'));
            }
        }
    }

    protected function schema(): ?array
    {
        if (static::$schema) {
            return static::$schema;
        }

        return static::$schema =
            collect(data_get($this->batchMetaData, 'headersMatched'))
                ->keyBy('matched_key')
                ->toArray();
    }
}
