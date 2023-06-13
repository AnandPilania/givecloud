<?php

namespace Tests\Browser\Themes\Social;

use Ds\Models\Post;
use Ds\Models\PostType;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class ShowSocialShareButtonsOnFeeds extends DuskTestCase
{
    /**
     * @return void
     */
    public function testShowSocialShareLinks()
    {
        $postType = PostType::factory()->create([
            'show_social_share_links' => true,
            'url_slug' => 'blog',
            'name' => 'Blog',
        ]);
        $post = Post::factory()->create();
        $postType->posts()->save($post);

        $this->browse(function (Browser $browser) use ($post) {
            $browser->visit($post->absolute_url)->assertSeeLink('Share with Facebook');
        });
    }

    public function testDontShowSocialShareLinks()
    {
        $postType = PostType::factory()->create([
            'show_social_share_links' => false,
            'url_slug' => 'blog',
            'name' => 'Blog',
        ]);
        $post = Post::factory()->create();
        $postType->posts()->save($post);

        $this->browse(function (Browser $browser) use ($post) {
            $browser->visit($post->absolute_url)->assertDontSeeLink('Share with Facebook');
        });
    }
}
