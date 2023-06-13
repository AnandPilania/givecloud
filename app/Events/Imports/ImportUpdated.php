<?php

namespace Ds\Events\Imports;

use Ds\Illuminate\Broadcasting\Channel;
use Ds\Models\Import;
use Ds\Services\Imports\ImportService;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Queue\SerializesModels;

class ImportUpdated implements ShouldBroadcast
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
        return 'import.updated';
    }

    public function broadcastOn(): Channel
    {
        return new Channel('imports.' . $this->import->id);
    }

    public function broadcastWith()
    {
        return app(ImportService::class)->toArray($this->import);
    }
}
