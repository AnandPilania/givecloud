<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use ArrayIterator;
use Countable;
use Ds\Domain\Theming\Liquid\Drop;
use Illuminate\Pagination\LengthAwarePaginator;
use IteratorAggregate;

class PaginateDrop extends Drop implements Countable, IteratorAggregate
{
    /**
     * Create an instance.
     *
     * @param \Illuminate\Pagination\LengthAwarePaginator $source
     * @param string $dropType
     */
    public function __construct(LengthAwarePaginator $source, string $dropType = null)
    {
        $this->source = $source;

        $this->initialize($source, $dropType);

        foreach ($this->attributes as $key) {
            $this->liquid[$key] = $source->getAttribute($key);
        }
    }

    /**
     * @param \Illuminate\Pagination\LengthAwarePaginator $source
     * @param string $dropType
     */
    protected function initialize($source, $dropType = null)
    {
        $this->liquid = [
            'current_offset' => ($source->currentPage() - 1) * $source->perPage(),
            'current_page' => $source->currentPage(),
            'items' => $source->total(),
            'data' => $this->getData($dropType),
            'parts' => $this->getParts($source),
            'next' => $this->getPart('Next »', $source->nextPageUrl()),
            'previous' => $this->getPart('« Previous', $source->previousPageUrl()),
            'page_size' => $source->perPage(),
            'pages' => $source->lastPage(),
        ];
    }

    protected function getData($dropType = null)
    {
        $collection = $this->source->getCollection();

        if ($dropType) {
            return Drop::collectionFactory($collection, $dropType);
        }

        return Drop::resolveData($collection);
    }

    protected function getParts($source)
    {
        $windowSize = 3;
        $pageCount = $source->lastPage();
        $currentPage = $source->currentPage();
        $parts = [];
        $ellipsis_break = false;

        if ($pageCount > 1) {
            foreach (range(1, $pageCount) as $page) {
                if ($currentPage == $page) {
                    $parts[] = $this->getPart($page);
                } elseif ($page == 1) {
                    $parts[] = $this->getPart($page, $source->url($page));
                } elseif ($page == $pageCount) {
                    $parts[] = $this->getPart($page, $source->url($page));
                } elseif ($page <= ($currentPage - $windowSize) || $page >= ($currentPage + $windowSize)) {
                    if ($ellipsis_break) {
                        continue;
                    }
                    $parts[] = $this->getPart('&hellip;');
                    $ellipsis_break = true;

                    continue;
                } else {
                    $parts[] = $this->getPart($page, $source->url($page));
                }
                $ellipsis_break = false;
            }
        }

        return $parts;
    }

    protected function getPart($title, $url = false)
    {
        if ($url) {
            return [
                'title' => $title,
                'url' => $url,
                'is_link' => true,
            ];
        }

        return [
            'title' => $title,
            'is_link' => false,
        ];
    }

    public function count(): int
    {
        return $this->source->getCollection()->count();
    }

    // required until support for Countables
    // is added to Liquid\Context
    public function size()
    {
        return $this->count();
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->getData());
    }
}
