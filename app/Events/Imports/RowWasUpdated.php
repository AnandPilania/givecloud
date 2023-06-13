<?php

namespace Ds\Events\Imports;

use Ds\Illuminate\Broadcasting\Channel;
use Ds\Models\Import;
use Ds\Services\Imports\ImportService;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class RowWasUpdated implements ShouldBroadcast
{
    use SerializesModels;

    /** @var \Ds\Models\Import */
    public $import;

    public function __construct(Import $import)
    {
        $this->import = $import;
    }

    public function broadcastAs()
    {
        return 'import.row.updated';
    }

    public function broadcastOn(): Channel
    {
        return new Channel('imports.' . $this->import->id);
    }

    public function broadcastWith()
    {
        return app(ImportService::class)->toArray($this->import);
    }

    public function broadcastWhen(): bool
    {
        if ($this->import->total_records < 100) {
            return true;
        }

        if ($this->import->current_record === $this->import->total_records) {
            return true;
        }

        // This event will only be broadcasted 100 times, to move progress bar. (100%, duh)
        // 51240 rows / 100 = 512,4 so send update each 512 rows.
        $modulo = floor($this->import->total_records / 100);

        return $this->import->current_record / $modulo === 0;
    }
}
