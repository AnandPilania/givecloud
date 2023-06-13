<?php

namespace Tests\Feature\Backend;

use Ds\Models\Post;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    public function testUnknownFeaturedImageHandledinUpdate(): void
    {
        $postId = Post::factory()->create()->getKey();

        $this->actingAsUser($this->createUserWithPermissions(['post.edit']));
        $response = $this->postJson(route('backend.posts.update'), [
            'id' => $postId,
            'featured_image_id' => 9999,
        ]);

        $response->assertRedirect(route('backend.posts.edit', ['i' => $postId]));
    }
}
