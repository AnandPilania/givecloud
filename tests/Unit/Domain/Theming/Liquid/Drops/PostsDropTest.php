<?php

namespace Tests\Unit\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drops\PostDrop;
use Ds\Domain\Theming\Liquid\Drops\PostsDrop;
use Ds\Models\Post;
use Tests\TestCase;

class PostsDropTest extends TestCase
{
    public function testLookupUsingId(): void
    {
        $post = Post::factory()->create();

        $postDrop = (new PostsDrop)->invokeDrop($post->id);

        $this->assertInstanceOf(PostDrop::class, $postDrop);
        $this->assertEquals($post->id, $postDrop->id);
    }

    public function testLookupUsingHandle(): void
    {
        $post = Post::factory()->create();

        $postDrop = (new PostsDrop)->invokeDrop($post->url_slug);

        $this->assertInstanceOf(PostDrop::class, $postDrop);
        $this->assertEquals($post->id, $postDrop->id);
    }
}
