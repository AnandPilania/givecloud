<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drop;
use Illuminate\Support\Str;

class LinkListDrop extends Drop
{
    protected $levels;
    protected $links = [];

    protected function initialize($source)
    {
        $this->liquid = [
            'handle' => Str::slug($source->title),
            'title' => $source->title,
        ];

        $source->loadMissing('children');

        foreach ($source->children as $child) {
            $this->links[] = new LinkDrop($child);
        }
    }

    /**
     * Returns the number of nested levels that a link contains.
     *
     * @return int
     */
    public function levels()
    {
        if ($this->levels === null) {
            $this->levels = 0;
            foreach ($this->links as $link) {
                $this->levels = max($this->levels, $this->levels + $link->levels());
            }
        }

        return $this->levels;
    }

    /**
     * Returns an array of the child links associated with the parent link.
     *
     * @return array<LinkDrop>
     */
    public function links()
    {
        return $this->links;
    }
}
