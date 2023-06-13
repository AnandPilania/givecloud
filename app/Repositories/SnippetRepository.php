<?php

namespace Ds\Repositories;

use Ds\Models\Post;
use Illuminate\Database\Eloquent\Builder;

class SnippetRepository
{
    public function find(int $id): ?Post
    {
        return $this->newSnippetQuery()->find($id);
    }

    public function findByName(string $name): ?Post
    {
        return $this->newSnippetQuery()
            ->where('post.name', $name)
            ->first();
    }

    private function newSnippetQuery(): Builder
    {
        return Post::query()
            ->select('post.*')
            ->join('posttype', 'posttype.id', 'post.type')
            ->where('posttype.sysname', 'snippet');
    }
}
