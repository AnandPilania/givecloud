<?php

namespace Ds\Models\Traits;

use Ds\Models\Comment;
use Ds\Models\User;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasComments
{
    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable');
    }

    /*
    * @return \Illuminate\Database\Eloquent\Model|false
    */
    public function comment(string $body, ?User $user = null)
    {
        $user = $user ?? auth()->user();

        $comment = new Comment([
            'body' => $body,
            'user_id' => $user->getKey(),
            'commentable_id' => $this->getKey(),
            'commentable_type' => $this->getMorphClass(),
        ]);

        return $this->comments()->save($comment);
    }
}
