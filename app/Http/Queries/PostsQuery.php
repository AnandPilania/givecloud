<?php

namespace Ds\Http\Queries;

use Ds\Models\Post;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PostsQuery extends QueryBuilder
{
    public function __construct($subject = null)
    {
        parent::__construct($subject ?? Post::query());

        $this
            ->allowedFilters([
                AllowedFilter::exact('id'),
                AllowedFilter::partial('name'),
            ])->allowedSorts([
                'published_on',
                'title',
            ])->allowedIncludes([
                'categories',
                'postType',
            ]);
    }
}
