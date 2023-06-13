<?php

namespace Ds\Models\Observers;

use Ds\Models\Post;

class PostObserver
{
    /**
     * Response to the creating/updating event.
     *
     * @param \Ds\Models\Post $model
     * @return void
     */
    public function saving(Post $model)
    {
        $model->modifieddatetime = fromUtc('now');
        $model->modifiedbyuserid = user('id') ?? 1;

        if (! $model->url_slug && $model->name) {
            $model->url_slug = $model->name;
        }
    }
}
