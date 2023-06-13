<?php

namespace Ds\Domain\Theming\Shortcodes;

use Ds\Domain\Theming\Shortcode;
use Ds\Models\Post;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;

class PostsShortcode extends Shortcode
{
    /**
     * Output the posts template.
     *
     * @param \Thunder\Shortcode\Shortcode\ShortcodeInterface $s
     * @return string
     */
    public function handle(ShortcodeInterface $s)
    {
        $type = $s->getParameter('type', '');
        $style = $s->getParameter('style', '');
        $orderby = $s->getParameter('orderby', 'published_at');
        $order = $s->getParameter('order', 'desc');
        $interval = $s->getParameter('interval', '5000');
        $categories = $s->getParameter('categories', '');
        $tags = $s->getParameter('tags', '');
        $limit = $s->getParameter('limit', '6');

        if (! $type) {
            return $this->error("'type' attribute required");
        }

        if (! $style) {
            return $this->error("'style' attribute required");
        }

        // only allow orderby: published_at or sequence
        if (! in_array($orderby, ['published_at', 'sequence'])) {
            $orderby = 'published_at';
        }

        // normalize published_at to database column name
        if ($orderby === 'published_at') {
            $orderby = 'postdatetime';
        }

        // only allow order: asc or desc
        if (! in_array($order, ['asc', 'desc'])) {
            $order = 'desc';
        }

        // cast the limit
        $limit = $limit ? min((int) $limit, 25) : 25;

        $postType = \Ds\Models\PostType::where('id', $type)->first();

        if ($postType) {
            $posts = $postType->activePosts()
                ->with('postType')
                ->select('post.*')
                ->take($limit);

            if ($postType->default_template_suffix === 'event' && $orderby === 'postdatetime') {
                $posts->leftJoin('metadata as eds', function ($join) {
                    $join->on('post.id', '=', 'eds.metadatable_id')
                        ->where('eds.metadatable_type', '=', (new Post)->getMorphClass())
                        ->where('eds.key', 'event_date_start');
                })->orderBy('eds.value', $order);
            } else {
                $posts->orderBy($orderby, $order);
            }

            $this->applyScopeForCategories($posts, $categories);

            $tags = collect(explode(',', $tags))
                ->map(function ($tag) {
                    return trim($tag);
                })->reject(function ($tag) {
                    return empty($tag);
                });

            if ($tags->count() > 0) {
                $posts->where(function ($q) use ($tags) {
                    foreach ($tags as $tag) {
                        $q->orWhere('tags', 'like', "%$tag%");
                    }
                });
            }

            $posts = $posts->get();
        } else {
            $posts = [];
        }

        $template = rtrim("templates/shortcodes/posts.$style", '.');
        $template = new \Ds\Domain\Theming\Liquid\Template($template);

        return $template->render([
            'id' => uniqid('posts-shortcode-'),
            'post_type' => $postType,
            'posts' => $posts,
            'interval' => $interval,
        ]);
    }

    private function applyScopeForCategories(HasMany $query, string $categories): void
    {
        $categories = collect(explode(',', $categories))
            ->map(function ($name) {
                return trim($name);
            })->reject(function ($name) {
                return empty($name);
            });

        if ($categories->isEmpty()) {
            return;
        }

        $query->whereHas('categories', function ($query) use ($categories) {
            $query->whereIn('id', $categories);
        });
    }
}
