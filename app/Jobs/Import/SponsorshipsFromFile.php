<?php

namespace Ds\Jobs\Import;

use Ds\Domain\Sponsorship\Models\PaymentOptionGroup;
use Ds\Domain\Sponsorship\Models\Segment;
use Ds\Domain\Sponsorship\Models\Sponsorship;
use Ds\Domain\Sponsorship\Models\SponsorshipSegment;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;

class SponsorshipsFromFile extends ImportJob
{
    /**
     * Column definition.
     */
    public function getColumnDefinitions(): Collection
    {
        $headers = collect([]);

        $headers->push((object) [
            'id' => 'reference_number',
            'name' => 'Reference Number',
            'validator' => 'required|max:25|string',
            'hint' => 'A unique number that identifies this record. Max 25 characters.',
        ]);

        $headers->push((object) [
            'id' => 'first_name',
            'name' => 'First Name',
            'validator' => 'required|max:45|string',
        ]);

        $headers->push((object) [
            'id' => 'last_name',
            'name' => 'Last Name',
            'validator' => 'nullable|max:45|string',
        ]);

        $headers->push((object) [
            'id' => 'gender',
            'name' => 'Gender',
            'validator' => 'nullable|in:M,F',
            'hint' => 'Must be the value \'M\' or \'F\'',
        ]);

        $headers->push((object) [
            'id' => 'birth_date',
            'name' => 'Birth Date',
            'validator' => 'nullable|date',
            'hint' => 'Must be formatted YYYY-MM-DD or MM/DD/YYYY. Try to be consistent with the format you choose.',
        ]);

        $headers->push((object) [
            'id' => 'enrollment_date',
            'name' => 'Enrollment Date',
            'validator' => 'nullable|date',
            'hint' => 'Must be formatted YYYY-MM-DD or MM/DD/YYYY. Try to be consistent with the format you choose.',
        ]);

        $headers->push((object) [
            'id' => 'private_notes',
            'name' => 'Private Note',
            'validator' => 'nullable|string',
            'hint' => 'A private note related to this record that only staff can view.',
        ]);

        $headers->push((object) [
            'id' => 'biography',
            'name' => 'Page Content/Biography',
            'validator' => 'nullable|string',
            'hint' => 'HTML formatted content for viewing on website.',
        ]);

        $headers->push((object) [
            'id' => 'is_sponsored',
            'name' => 'Is Sponsored',
            'validator' => 'nullable|in:Y,N,Yes,No',
            'hint' => 'Must be the value \'Y\' or \'N\'. If blank, status of the sponsorship will be based on linked sponsors.',
        ]);

        $headers->push((object) [
            'id' => 'is_enabled',
            'name' => 'Display on Website',
            'validator' => 'nullable|in:Y,N,Yes,No',
            'hint' => 'Must be the value \'Y\' or \'N\'. If blank, \'N\' is assumed.',
            'default' => 'N',
        ]);

        $headers->push((object) [
            'id' => 'payment_option_group',
            'name' => 'Payment Option',
            'validator' => 'nullable|max:150|string',
            'hint' => 'The name of Payment Option for this record. Max 150 characters.',
        ]);

        $headers->push((object) [
            'id' => 'latitude',
            'name' => 'Latitude',
            'validator' => 'nullable|numeric',
            'hint' => 'The location coordinate for where the child is located.',
        ]);

        $headers->push((object) [
            'id' => 'longitude',
            'name' => 'Longitude',
            'validator' => 'nullable|numeric',
            'hint' => 'The location coordinate for where the child is located.',
        ]);

        $headers->push((object) [
            'id' => 'meta1',
            'name' => 'GL Code',
            'validator' => 'nullable|string|max:200',
            'hint' => 'Optional. General ledger code for accounting and reporting.',
        ]);

        // if DP is integrated, show DP columns
        if (dpo_is_enabled()) {
            // three basic DP fields
            $headers->push((object) [
                'id' => 'meta2',
                'name' => 'Campaign',
                'validator' => 'nullable|string|max:200',
                'hint' => 'Campaign code for accounting and reporting.',
            ]);

            $headers->push((object) [
                'id' => 'meta3',
                'name' => 'Solicitation',
                'hint' => 'Solicitation code for accounting and reporting.',
            ]);

            $headers->push((object) [
                'id' => 'meta4',
                'name' => 'Sub Solicitation',
                'validator' => 'nullable|string|max:200',
                'hint' => 'Sub solicitation code for accounting and reporting.',
            ]);

            // custom dp fields
            for ($ix = 9; $ix <= 23; $ix++) {
                // skip blank records
                if (trim(sys_get('dp_meta' . $ix . '_label')) == '') {
                    continue;
                }

                // add each custom dp record
                $headers->push((object) [
                    'id' => 'meta' . $ix,
                    'name' => sys_get('dp_meta' . $ix . '_label'),
                    'validator' => 'nullable|string|max:200',
                    'hint' => 'DonorPerfect User-Defined Field.',
                ]);
            }
        }

        // loop over custom segments and add each segment record
        foreach (Segment::active()->orderBy('id')->with('items')->get() as $segment) {
            $option_values = ($segment->items->count() > 0) ? $segment->items->pluck('name')->toArray() : null;

            $headers->push((object) [
                'id' => 'segment_' . $segment->id,
                'name' => $segment->name,
                'validator' => [
                    'nullable',
                    ($option_values) ? Rule::in($option_values) : 'string',
                ],
                'hint' => (($segment->show_in_detail) ? 'Public. ' : 'Private. ') . $segment->description . (($option_values) ? ' Must be exactly one of: \'' . implode('\' or \'', $option_values) . '\'' : ''),
            ]);
        }

        return $headers;
    }

    /**
     * Analyze a row.
     *
     * @param array $row
     */
    public function analyzeRow(array $row)
    {
        $messages = [];

        $sponsorship = Sponsorship::where('reference_number', $row['reference_number'])->first();

        if ($sponsorship) {
            $messages[] = 'An existing record will be updated. (' . $row['reference_number'] . ' - ' . $row['first_name'] . '  ' . $row['last_name'] . ')';
        }

        return (count($messages)) ? implode('', $messages) : null;
    }

    /**
     * Import a rows.
     *
     * @param array $row
     */
    public function importRow(array $row)
    {
        $segments = [];
        $paymentOptionGroup = null;

        $sponsorship = Sponsorship::where('reference_number', $row['reference_number'])->first();
        $existed = true;

        // create new sponsorship record (make sure there are no default values for sponsored and enabled)
        if (! $sponsorship) {
            $sponsorship = new Sponsorship([
                'is_sponsored' => null,
                'is_enabled' => null,
            ]);
            $existed = false;
        }

        foreach ($this->getColumnDefinitions() as $column) {
            $cell_value = $row[$column->id];

            // skip the data if its null
            if ($existed && ! isset($cell_value)) {
                continue;
            }

            // collect Segments to be imported after the Sponsorship has been created
            if (Str::startsWith($column->id, 'segment_')) {
                $segment_id = (int) explode('_', $column->id)[1];

                $segment = $this->getSegments()->where('id', $segment_id)->first();
                $segment_item = null;

                // if this segment type is multi-select
                if ($segment && ($segment->type === 'multi-select' || $segment->type === 'advanced-multi-select') && $segment->items) {
                    // Try to find a matching option in the segment item relationship.
                    // Use Str::slug to strip out bad characters, white-space, etc so we
                    // have as loose a match as possible.
                    // We want " england uk" to match "England UK"
                    $segment_item = $segment->items
                        ->filter(function ($item) use ($cell_value) {
                            return Str::slug($item->name) == Str::slug($cell_value);
                        })->first();

                    // if we couldn't match a segment option, skip this heading
                    if (! $segment_item) {
                        continue;
                    }
                }

                // as long as there is a value to insert
                if ($cell_value || $segment_item) {
                    $segments[] = [
                        'sponsorship_id' => null,
                        'segment_id' => $segment_id,
                        'segment_item_id' => $segment_item ? $segment_item->id : null,
                        'value' => $cell_value,
                        'created_by' => $this->import->created_by,
                        'updated_by' => $this->import->updated_by,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            } else {
                try {
                    // hack - truth and gender casts are not processing SET ATTRIBUTE
                    // cast only impacts ACCESSING attributes
                    if ($column->id === 'gender') {
                        $sponsorship->gender = Str::genderize($cell_value);
                    } elseif ($column->id === 'is_sponsored') {
                        $sponsorship->is_sponsored = Str::boolify($cell_value);
                        $sponsorship->is_sponsored_auto = ($cell_value === null);
                    } elseif ($column->id === 'is_enabled') {
                        $sponsorship->is_enabled = Str::boolify($cell_value);
                    } elseif ($column->id === 'payment_option_group') {
                        if (empty($cell_value)) {
                            continue;
                        }

                        $paymentOptionGroup = PaymentOptionGroup::firstOrCreate(['name' => $cell_value]);
                    } else {
                        $sponsorship->setAttribute($column->id, $cell_value);
                    }
                } catch (Throwable $e) {
                    // do nothing
                }
            }
        }

        $sponsorship->updateIsSponsored(false);
        $sponsorship->save();

        if ($paymentOptionGroup) {
            $sponsorship->paymentOptionGroups()->sync($paymentOptionGroup);
        }

        if (count($segments)) {
            foreach ($segments as &$segment) {
                $segment['sponsorship_id'] = $sponsorship->id;
            }

            SponsorshipSegment::query()->insert($segments);

            $this->getSegments(true);
        }

        return $existed ? 'updated_records' : 'added_records';
    }

    /**
     * Retrieve segments.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getSegments($forceRefresh = false)
    {
        static $segments;

        if ($forceRefresh || ! $segments) {
            $segments = Segment::with('items')->get();
        }

        return $segments;
    }
}
