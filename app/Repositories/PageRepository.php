<?php

namespace Ds\Repositories;

use Ds\Models\Node;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class PageRepository
{
    /**
     * @param int|array $id
     * @return \Ds\Models\Node|null
     */
    public function find($id): ?Node
    {
        return $this->getActivePagesBuilder()->find($id);
    }

    public function findByUrl(string $url): ?Node
    {
        return $this->getActivePagesBuilder()
            ->whereIn('node.url', [$url, "/$url", "$url.php", "/$url.php"])
            ->first();
    }

    private function getActivePagesBuilder(): Builder
    {
        return Node::query()
            ->whereIn('node.type', ['html', 'advanced', 'liquid'])
            ->where('node.isactive', 1);
    }

    public function getPageList(int $parent = null): Collection
    {
        $pages = Node::query()
            ->select('id', 'title')
            ->where('isactive', 1)
            ->where('protected', '!=', 1)
            ->when(
                $parent === null,
                function ($query) {
                    $query->whereNull('parentid');
                    $query->orWhere('parentid', 0);
                },
                function ($query) use ($parent) {
                    $query->where('parentid', $parent);
                },
            )->orderBy('sequence')
            ->toBase()
            ->get();

        $pages->each(function ($page) {
            $page->pages = $this->getPageList($page->id);
        });

        return $pages;
    }
}
