<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drop;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Str;

class LinkDrop extends Drop
{
    protected $levels;
    protected $child_active;
    protected $object;
    protected $links = [];

    protected function initialize($source)
    {
        $this->liquid = [
            'id' => $source->id,
            'title' => $source->title,
            'url' => $source->abs_url,
            'share_url' => $source->share_url,
            'target' => $source->target ?: null,
        ];

        $source->loadMissing('children');

        foreach ($source->children as $child) {
            $this->links[] = new LinkDrop($child);
        }
    }

    /**
     * Returns true if the link is active, or false if the link is inactive.
     *
     * @return bool
     */
    public function active()
    {
        $path = ltrim($this->source->absUrl(false), '/');

        if (empty($path)) {
            return false;
        }

        return Request::is($path);
    }

    /**
     * Similar to link.active, but returns true if a child link of the parent link is active,
     * or false if no child links of the parent link are active.
     *
     * @return bool
     */
    public function child_active()
    {
        if ($this->child_active === null) {
            foreach ($this->links as $link) {
                if ($link->active() || $link->child_active()) {
                    $this->child_active = true;
                    break;
                }
            }
        }

        return $this->child_active;
    }

    /**
     * Returns the number of nested levels that a link contains.
     *
     * @return int
     */
    public function levels()
    {
        if ($this->levels === null) {
            if (count($this->links)) {
                $this->levels = 1;
                foreach ($this->links as $link) {
                    $this->levels = max($this->levels, $this->levels + $link->levels());
                }
            } else {
                $this->levels = 0;
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

    /**
     * Returns the variable associated to the link. Through link.object, you can access any of the
     * attributes that are available in the above three variables.
     *
     * @return \Ds\Models\Node|\Ds\Models\ProductCategory|\Ds\Models\Product|void
     */
    public function object()
    {
        if ($this->object) {
            return $this->object;
        }

        if ($this->type() === 'collection_link') {
            return $this->object = $this->source->category;
        }

        if ($this->type() === 'page_link') {
            return $this->object = $this->source;
        }

        if ($this->type() === 'product_link') {
            $code = preg_replace('#/products/([^/]+)/.*$#', '$1', $this->source->url);

            return $this->object = \Ds\Models\Product::whereCode(urldecode($code))->first();
        }
    }

    /**
     * Returns the type of the link.
     *
     * @return string|void
     */
    public function type()
    {
        if ($this->source->type === 'category') {
            return 'collection_link';
        }

        if ($this->source->type === 'advanced' || $this->source->type === 'html') {
            return 'page_link';
        }

        if ($this->source->type === 'menu') {
            if (Str::startsWith($this->source->url, '/product/')) {
                return 'product_link';
            }
            if (Str::startsWith($this->source->url, '/') && ! Str::startsWith($this->source->url, '//')) {
                return 'relative_link';
            }

            return 'http_link';
        }
    }
}
