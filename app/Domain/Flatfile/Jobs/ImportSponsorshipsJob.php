<?php

namespace Ds\Domain\Flatfile\Jobs;

use Ds\Domain\Sponsorship\Models\PaymentOptionGroup;
use Ds\Domain\Sponsorship\Models\Segment;
use Ds\Domain\Sponsorship\Models\Sponsorship;
use Ds\Domain\Sponsorship\Models\SponsorshipSegment;
use Illuminate\Support\Str;
use Throwable;

class ImportSponsorshipsJob extends ImportJob
{
    protected function importRow(array $row): void
    {
        if (! $sponsorship = Sponsorship::where('reference_number', $row['reference_number'])->first()) {
            $sponsorship = new Sponsorship([
                'is_sponsored' => null,
                'is_enabled' => false,
            ]);
        }
        $segments = collect();

        foreach ($this->schema() as $definition) {
            $column = $definition['matched_key'];

            if (! isset($row[$column])) {
                continue;
            }

            $value = $row[$column];

            if (Str::startsWith($column, 'custom_')) {
                continue;
            }

            try {
                if (Str::startsWith($column, 'segment_')) {
                    $segments->push($this->segment($column, $value));
                } elseif ($column === 'gender') {
                    $sponsorship->gender = Str::genderize($value);
                } elseif ($column === 'is_sponsored') {
                    $sponsorship->is_sponsored = Str::boolify($value);
                    $sponsorship->is_sponsored_auto = ($value === null);
                } elseif ($column === 'is_enabled') {
                    $sponsorship->is_enabled = Str::boolify($value);
                } elseif ($column === 'payment_option_group') {
                    if (empty($value)) {
                        continue;
                    }
                    $paymentOptionGroup = PaymentOptionGroup::firstOrCreate(['name' => $value]);
                } else {
                    $sponsorship->setAttribute($column, $value);
                }
            } catch (Throwable $e) {
                // do nothing
            }
        }

        if (is_null($sponsorship->is_enabled)) {
            $sponsorship->is_enabled = false;
        }

        $sponsorship->updateIsSponsored(false);
        $sponsorship->save();

        if (isset($paymentOptionGroup)) {
            $sponsorship->paymentOptionGroups()->sync($paymentOptionGroup);
        }

        if ($segments->isEmpty()) {
            return;
        }

        $segments = $segments->filter()
            ->map(function (array $segment) use ($sponsorship) {
                $segment['sponsorship_id'] = $sponsorship->id;

                return $segment;
            });

        SponsorshipSegment::query()->upsert($segments->toArray(), [
            'sponsorship_id', 'segment_id',
        ], [
            'segment_item_id',
            'value',
            'created_by',
            'updated_by',
            'created_at',
            'updated_at',
        ]);
    }

    protected function segment(string $column, string $value): ?array
    {
        $segmentId = Str::after($column, '_');

        $segment = Segment::query()->with('items')->where('id', $segmentId)->first();
        $segmentItem = null;

        if (optional($segment)->items && in_array($segment->type, ['multi-select', 'advanced-multi-select'])) {
            $segmentItem = $segment->items
                ->first(function ($item) use ($value) {
                    return Str::slug($item->name) === Str::slug($value);
                });

            // if we couldn't match a segment option, skip this heading
            if (! $segmentItem) {
                return null;
            }
        }

        // as long as there is a value to insert
        if ($value || $segmentItem) {
            $segment = [
                'sponsorship_id' => null,
                'segment_id' => $segmentId,
                'segment_item_id' => optional($segmentItem)->id,
                'value' => $value,
                'created_by' => data_get($this->batchMetaData, '__endUser__.userId'),
                'updated_by' => data_get($this->batchMetaData, '__endUser__.userId'),
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        return $segment;
    }
}
