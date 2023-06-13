<?php

namespace Ds\Domain\Salesforce\Services;

use Ds\Domain\Salesforce\Database\Repository;
use Ds\Domain\Salesforce\Models\Model as SalesforceModel;
use Ds\Enums\ExternalReference\ExternalReferenceService;
use Ds\Models\ExternalReference;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

abstract class SalesforceSyncService
{
    protected string $externalType;
    protected string $localObject;
    protected string $object;

    public function shouldSync(): bool
    {
        return app(SalesforceClientService::class)->isEnabled();
    }

    public function upsert(Model $localObject): SalesforceModel
    {
        /** @var \Ds\Domain\Salesforce\Models\Model $object */
        $object = $this->salesforceObjectFromModel($localObject);

        $salesforceModel = app(Repository::class)->firstOrCreate(
            $this->object,
            [$object->externalKey => $object->getCompoundKey()],
            $object->mapFields()
        );

        $this->updateLocalReferences(collect([$localObject]));

        return $salesforceModel;
    }

    public function upsertMultiple(Collection $models): array
    {
        $results = app(Repository::class)->upsertRecords(
            $this->object,
            $models->map(function (Model $model) {
                $object = $this->salesforceObjectFromModel($model);

                return $object->mapFields(true);
            })->values()->all()
        );

        $this->updateLocalReferences($models);

        return $results;
    }

    public function updateLocalReferences(Collection $models): ?int
    {
        if (! (new $this->object)->savesExternalReferenceLocally() || empty($this->externalType)) {
            return null;
        }

        $updated = app(Repository::class)
            ->findByLocalKeys($this->object, $models->map(function (Model $model) {
                return $this->salesforceObjectFromModel($model)->getCompoundKey();
            }))
            ->map(function ($row) {
                return [
                    'type' => $this->externalType,
                    'service' => ExternalReferenceService::SALESFORCE,
                    'referenceable_type' => (new $this->localObject)->getMorphClass(),
                    'referenceable_id' => (int) preg_replace('/[^0-9.]/', '', $row->{(new $this->object)->externalKey}),
                    'reference' => $row->Id,
                ];
            })->all();

        return $this->upsertReferences($updated);
    }

    public function upsertReferences(array $references): int
    {
        return ExternalReference::query()->upsert(
            $references,
            ['type', 'service', 'referenceable_type', 'referenceable_id'],
            ['reference'],
        );
    }

    public function salesforceObjectFromModel(Model $localObject): SalesforceModel
    {
        return (new $this->object)->forModel($localObject);
    }
}
