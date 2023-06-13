<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drop;
use Ds\Models\Post;
use Illuminate\Support\Str;

class PostsDrop extends Drop
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
        $post = Post::active();

        if (Str::contains($method, ',')) {
            $ids = collect(explode(',', $method))
                ->map(fn ($name) => trim($name))
                ->reject(fn ($name) => empty($name))
                ->map(fn ($name) => (int) $name)
                ->all();

            return $post->orderBySet('id', $ids)->find($ids);
        }

        if (is_numeric($method)) {
            return $post->find($method);
        }

        return $post->where('url_slug', $method)->first();
    }
}
