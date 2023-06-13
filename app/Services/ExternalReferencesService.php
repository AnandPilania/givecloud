<?php

namespace Ds\Services;

use Ds\Enums\ExternalReference\ExternalReferenceService;
use Ds\Enums\ExternalReference\ExternalReferenceType;
use Ds\Models\ExternalReference;
use Ds\Models\Order;
use Ds\Models\OrderItem;
use Ds\Models\Transaction;
use Illuminate\Database\Eloquent\Model;

class ExternalReferencesService
{
    public const CODING_PATTERN = '#GC:\d+:(?P<model>ITEM|ORDER|TXN):(?:(?P<type>TXNSPLIT|DCC|PLEDGE|SHIP|TAX):)?(?P<id>\d+)#i';

    public function deleteByCoding(string $coding, string $service = ExternalReferenceService::DONOR_PERFECT): void
    {
        $reference = $this->getReferenceModelByCoding($coding, $service);

        if ($reference) {
            $reference->delete();
        }
    }

    public function upsert(
        Model $model,
        string $reference,
        string $type = ExternalReferenceType::ORDER,
        string $service = ExternalReferenceService::DONOR_PERFECT
    ): Model {
        return ExternalReference::query()->updateOrCreate([
            'referenceable_id' => $model->getKey(),
            'referenceable_type' => $model->getMorphClass(),
            'type' => $type,
            'service' => $service,
        ], ['reference' => $reference]);
    }

    public function getReferenceByCoding(string $coding, string $service = ExternalReferenceService::DONOR_PERFECT): ?string
    {
        $reference = $this->getReferenceModelByCoding($coding, $service);

        if (! is_null($reference)) {
            return $reference->reference;
        }

        return null;
    }

    public function getReferenceModelByCoding(string $coding, string $service = ExternalReferenceService::DONOR_PERFECT): ?Model
    {
        $definitions = $this->getDefinitionsForCoding($coding);

        if (! $definitions) {
            return null;
        }

        $referenceable = new $definitions['model'];

        return ExternalReference::query()
            ->where('referenceable_id', $definitions['id'])
            ->where('referenceable_type', $referenceable->getMorphClass())
            ->where('type', $definitions['type'])
            ->where('service', $service)
            ->latest()
            ->first();
    }

    public function getDefinitionsForCoding(string $coding): ?array
    {
        if (! preg_match(static::CODING_PATTERN, $coding, $matches)) {
            return null;
        }

        $referenceTypeModels = [
            ExternalReferenceType::ORDER => Order::class,
            ExternalReferenceType::ITEM => OrderItem::class,
            ExternalReferenceType::TXN => Transaction::class,
        ];

        return [
            'model' => $referenceTypeModels[$matches['model']],
            'id' => $matches['id'],
            'type' => $matches['type'] ?: $matches['model'],
        ];
    }
}
