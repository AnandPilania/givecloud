<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drop;
use Ds\Models\Node;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;

class LinkListsDrop extends Drop
{
    const SOURCE_REQUIRED = false;

    /**
     * Catch all method that is invoked before a specific method
     *
     * @param string $method
     * @return mixed
     */
    protected function liquidMethodMissing($method)
    {
        $menus = Node::query()
            ->where('isactive', 1)
            ->menus();

        if (is_numeric($method)) {
            try {
                return new LinkListDrop($menus->findOrFail($method));
            } catch (ModelNotFoundException $e) {
                return null;
            }
        }

        foreach ($menus->cursor() as $menu) {
            if ($method === Str::slug($menu->title)) {
                return new LinkListDrop($menu);
            }
        }

        return null;
    }
}
