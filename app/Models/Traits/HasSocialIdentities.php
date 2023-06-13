<?php

namespace Ds\Models\Traits;

use Ds\Models\SocialIdentity;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasSocialIdentities
{
    public function socialIdentities(): MorphMany
    {
        return $this->morphMany(SocialIdentity::class, 'authenticatable');
    }

    public function latestSocialIdentity(): MorphOne
    {
        return $this->morphOne(SocialIdentity::class, 'authenticatable')
            ->latestOfMany('updated_at');
    }

    public function latestAvatar(): MorphOne
    {
        /*
         * Facebook deprecated its tokenless access to User Pictures, thus skipping FB
         * https://developers.facebook.com/blog/post/2020/08/04/Introducing-graph-v8-marketing-api-v8
         */
        return $this->latestSocialIdentity()
            ->whereNotNull('avatar')
            ->where('provider_name', '!=', 'facebook');
    }

    public function getAvatarAttribute(): ?string
    {
        return data_get($this->latestAvatar, 'avatar');
    }
}
