<?php

namespace Ds\Domain\Flatfile\Services;

use Ds\Domain\Sponsorship\Models\Segment;
use Firebase\JWT\JWT;
use Illuminate\Support\Carbon;

class Sponsorships
{
    public function token(): string
    {
        return JWT::encode([
            'embed' => config('services.flatfile.embeds.sponsorships.id'),
            'user' => [
                'id' => auth()->user()->id,
                'email' => auth()->user()->email,
                'name' => auth()->user()->name,
            ],
            'org' => [
                'id' => site()->client->id,
                'name' => sys_get('ds_account_name'),
            ],
            'env' => [
                'cache_buster' => Carbon::now()->unix(),
                'account_name' => sys_get('ds_account_name'),
                'callback' => route('flatfile.webhook.sponsorships'),
                'segment_validation_rules' => $this->segmentValidationRules(),
            ],
        ], config('services.flatfile.embeds.sponsorships.key'), 'HS256');
    }

    public function customFields(): array
    {
        return array_merge(
            $this->dpFields(),
            $this->dpUserDefinedFields(),
            $this->customSegments()
        );
    }

    public function segmentValidationRules(): array
    {
        return
            Segment::active()->orderBy('id')
                ->with('items')
                ->get()
                ->filter(fn (Segment $segment) => $segment->items->isNotEmpty())
                ->mapWithKeys(function (Segment $segment) {
                    $options = $segment->items->pluck('name');

                    return ['segment_' . $segment->id => $options];
                })->toArray();
    }

    public function dpUserDefinedFields(): array
    {
        $fields = [];

        for ($ix = 9; $ix <= 23; $ix++) {
            // skip blank records
            if (trim(sys_get('dp_meta' . $ix . '_label')) === '') {
                continue;
            }

            $fields[] = [
                'field' => 'meta' . $ix,
                'label' => sys_get('dp_meta' . $ix . '_label'),
                'type' => 'string',
                'description' => 'DonorPerfect User-Defined Field.',
            ];
        }

        return $fields;
    }

    public function dpFields(): array
    {
        if (! dpo_is_enabled()) {
            return [];
        }

        return [
            [
                'field' => 'meta2',
                'label' => 'Campaign',
                'type' => 'string',
                'description' => 'Campaign code for accounting and reporting.',
            ], [
                'field' => 'meta3',
                'label' => 'Solicitation',
                'type' => 'string',
                'description' => 'Solicitation code for accounting and reporting.',
            ], [
                'field' => 'meta4',
                'label' => 'Sub Solicitation',
                'type' => 'string',
                'description' => 'Sub solicitation code for accounting and reporting.',
            ],
        ];
    }

    public function customSegments(): array
    {
        return
            Segment::active()->orderBy('id')
                ->with('items')
                ->get()
                ->map(function (Segment $segment) {
                    $options = $segment->items->pluck('name');

                    $description = sprintf(
                        '%s %s %s',
                        $segment->show_in_detail ? 'Public.' : 'Private.',
                        $segment->description,
                        $options->isNotEmpty() ? 'Must be exactly one of: ' . $options->implode(', ') : ''
                    );

                    return [
                        'field' => 'segment_' . $segment->id,
                        'label' => $segment->name,
                        'type' => 'string',
                        'description' => preg_replace('/\s+/', ' ', trim($description)),
                    ];
                })->toArray();
    }
}
