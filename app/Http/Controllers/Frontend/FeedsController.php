<?php

namespace Ds\Http\Controllers\Frontend;

use Ds\Models\Post;
use Ds\Models\PostType;

class FeedsController extends Controller
{
    /**
     * Register controller middleware.
     */
    protected function registerMiddleware()
    {
        $this->middleware('requires.feature:givecloud_pro');
    }

    public function index()
    {
        $feed = PostType::findOrFail(request('i'));

        $posts = $feed->activePosts()->orderBy('sequence')->orderBy('postdatetime')->cursor();

        return response()
            ->view('frontend/feed', [
                'feed' => $feed,
                'posts' => $posts,
            ])->header('Content-Type', 'text/xml');
    }

    /**
     * Handle a request for a post type.
     *
     * @param \Ds\Models\PostType $postType
     */
    public function handlePostType(PostType $postType)
    {
        // layout we're forcing theme developers to extend
        pageSetup($postType->name, 'content', 4);

        $filters = [
            'per_page' => min(100, request('per_page', 24)),
            'keywords' => request('keywords'),
            'order_by' => request('order_by'),
            'categories' => request('categories') ? explode(',', request('categories')) : [],
        ];

        // posts
        $posts_query = $postType->activePosts()
            ->with('postType')
            ->select('post.*');

        if (count($filters['categories'])) {
            $posts_query->whereHas('categories', function ($query) use ($filters) {
                $query->whereIn('id', $filters['categories']);
            });
        }

        if ($postType->default_template_suffix === 'event') {
            $posts_query->leftJoin('metadata as eds', function ($join) {
                $join->on('post.id', '=', 'eds.metadatable_id')
                    ->where('eds.metadatable_type', '=', (new Post)->getMorphClass())
                    ->where('eds.key', 'event_date_start');
            })->orderBy('eds.value', 'desc');
        } else {
            $posts_query->orderBy('postdatetime', 'desc');
        }

        // for map view, we need all posts
        $locations = ($postType->template_suffix == 'map-view') ? $posts_query->get() : collect([]);

        if ($filters['keywords']) {
            $posts_query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['keywords'] . '%');
                $q->orWhere('description', 'like', '%' . $filters['keywords'] . '%');
            });
        }

        // pagination
        $posts = $posts_query->paginate($filters['per_page']);

        // Repushing original request categories in filters
        $filters['categories'] = request('categories');

        // render template
        return $this->renderTemplate("post-type.{$postType->template_suffix}", [
            'post_type' => $postType,
            'posts' => $posts,
            'filters' => $filters,
            'locations' => $locations,
            'pagination' => get_pagination_data($posts, $filters),
        ]);
    }

    /**
     * Handle a request for a post type.
     *
     * @param \Ds\Models\PostType $postType
     */
    public function handlePostTypeCategory($handle, PostType $postType)
    {
        // layout we're forcing theme developers to extend
        pageSetup($postType->name, 'content', 4);

        // category
        $category = $postType->categories()
            ->where('handle', $handle)
            ->firstOrFail();

        // posts
        $posts_query = $postType->activePosts()
            ->with('postType.categories')
            ->select('post.*')
            ->whereHas('categories', function ($categories) use ($handle) {
                $categories->where('handle', $handle);
            });

        if ($postType->default_template_suffix === 'event') {
            $posts_query->leftJoin('metadata as eds', function ($join) {
                $join->on('post.id', '=', 'eds.metadatable_id')
                    ->where('eds.metadatable_type', '=', (new Post)->getMorphClass())
                    ->where('eds.key', 'event_date_start');
            })->orderBy('eds.value', 'desc');
        } else {
            $posts_query->orderBy('postdatetime', 'desc');
        }

        // for map view, we need all posts
        $locations = ($postType->template_suffix == 'map-view') ? $posts_query->get() : collect([]);

        // pagination
        $posts = $posts_query->paginate(24);

        // render template
        return $this->renderTemplate("post-type.{$postType->template_suffix}", [
            'post_type' => $postType,
            'posts' => $posts,
            'category' => $category,
            'locations' => $locations,
            'pagination' => get_pagination_data($posts, []),
        ]);
    }

    /**
     * Handle a request for a post.
     *
     * @param string $slug
     * @param \Ds\Models\PostType $post_type
     */
    public function handlePost($slug, PostType $post_type)
    {
        $post = $post_type->activePosts()
            ->where('url_slug', $slug)
            ->firstOrFail();

        // layout we're forcing theme developers to extend
        pageSetup($post->name, 'content', 4);

        $rec_posts = $post_type->activePosts()
            ->whereNotIn('id', [$post->id])
            ->inRandomOrder()
            ->take(10)
            ->get();

        $templateSuffix = $post->template_suffix ?? $post_type->default_template_suffix;

        // render template
        return $this->renderTemplate("post.$templateSuffix", compact('post', 'post_type', 'rec_posts'));
    }
}
