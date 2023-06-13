<?php

namespace Ds\Events;

use Ds\Eloquent\UploadableMedia;
use Illuminate\Queue\SerializesModels;

class MediaUploaded extends Event
{
    use SerializesModels;

    /** @var \Ds\Eloquent\UploadableMedia */
    public $item;

    /**
     * Create a new event instance.
     *
     * @param \Ds\Eloquent\UploadableMedia $item
     * @return void
     */
    public function __construct(UploadableMedia $item)
    {
        $this->item = $item;
    }
}
