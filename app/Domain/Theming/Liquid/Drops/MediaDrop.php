<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drop;

class MediaDrop extends Drop
{
    /** @var array */
    protected $mutators = [
        'is_audio',
        'is_image',
        'is_video',
    ];

    protected function initialize($source)
    {
        $this->liquid = [
            'id' => $source->id,
            'full' => $source->public_url,
            'thumb' => $source->thumbnail_url,
            'content_type' => $source->content_type,
            'caption' => $source->caption,
        ];
    }

    /**
     * Output as string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->liquid['full'];
    }
}
