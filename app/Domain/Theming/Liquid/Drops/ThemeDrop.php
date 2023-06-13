<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drop;

class ThemeDrop extends Drop
{
    /** @var \Ds\Models\Theme */
    protected $source;

    /**
     * @param \Ds\Models\Theme $source
     */
    protected function initialize($source)
    {
        $this->liquid = [
            'id' => $source->id,
            'name' => $source->title,
        ];
    }

    /**
     * Output as string.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->liquid['name'];
    }
}
