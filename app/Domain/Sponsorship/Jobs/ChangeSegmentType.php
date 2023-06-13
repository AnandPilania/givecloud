<?php

namespace Ds\Domain\Sponsorship\Jobs;

use Ds\Domain\Sponsorship\Models\SponsorshipSegment;
use Ds\Jobs\Job;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ChangeSegmentType extends Job implements ShouldQueue
{
    use InteractsWithQueue;
    use SerializesModels;

    /** @var int */
    protected $segmentId;

    /** @var string */
    protected $currentType;

    /** @var string */
    protected $newType;

    /**
     * Create a new job instance.
     *
     * @param int $segmentId
     * @param string $currentType
     * @param string $newType
     * @return void
     */
    public function __construct(int $segmentId, string $currentType, string $newType)
    {
        $this->segmentId = $segmentId;
        $this->currentType = $currentType;
        $this->newType = $newType;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $sponsorshipSegments = SponsorshipSegment::query()
            ->where('segment_id', $this->segmentId)
            ->where(function ($query) {
                $query->whereNotNull('value');
                $query->orWhereNotNull('segment_item_id');
            })->with('item');

        foreach ($sponsorshipSegments->cursor() as $sponsorshipSegment) {
            $value = $sponsorshipSegment->value;

            // Skip, no data
            if (empty($value)) {
                continue;
            }

            // Skip, no changes required
            if (in_array($this->currentType, ['multi-select', 'advanced-multi-select']) &&
                in_array($this->newType, ['multi-select', 'advanced-multi-select'])
            ) {
                continue;
            }

            // Attempt to conform value to expected date format
            // however if not parseable do not overwrite the value
            if ($this->newType === 'date') {
                $value = fromDateFormat($value, 'date') ?: $value;
            }

            $sponsorshipSegment->value = $value ?: null;
            $sponsorshipSegment->save();
        }
    }
}
