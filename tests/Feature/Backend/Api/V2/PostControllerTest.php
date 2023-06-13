<?php

namespace Tests\Feature\Backend\Api\V2;

use Ds\Models\Post;
use Ds\Models\PostType;
use Ds\Models\User;
use Tests\TestCase;

class PostControllerTest extends TestCase
{
    public function testLoggedInUserWithPermissionCanGetPosts(): void
    {
        $user = $this->createUserWithPermissions(['post.']);
        $feed = PostType::factory()->withPhoto()->create();
        $feed->posts()->saveMany(Post::factory(3)->withTags()->withImages()->make());

        $response = $this
            ->actingAsPassportUser($user)
            ->getJson(route('admin.api.v2.feeds.posts.index', $feed->hash_id));

        $response->assertOk();
    }

    public function testGuestUserCannotGetPosts(): void
    {
        $feed = PostType::factory()->create();

        $response = $this->getJson(route('admin.api.v2.feeds.posts.index', $feed->hash_id));

        $response->assertUnauthorized();
    }

    public function testLoggedInUserWithoutPermissionCannotGetPosts(): void
    {
        $feed = PostType::factory()->create();

        $response = $this
            ->actingAsPassportUser()
            ->getJson(route('admin.api.v2.feeds.posts.index', $feed->hash_id));

        $response->assertForbidden();
    }

    public function testLoggedInUserWithPermissionCanGetPost(): void
    {
        $user = User::factory()->admin()->create();
        $feed = PostType::factory()->create();
        $post = Post::factory()->make(['name' => 'Test Post']);
        $feed->posts()->save($post);

        $response = $this
            ->actingAsPassportUser($user)
            ->getJson(route('admin.api.v2.feeds.posts.show', [$feed->hash_id, $post->hash_id]));

        $response->assertJsonFragment(['name' => 'Test Post']);
    }

    public function testLoggedInUserWithPermissionCannotGetPostWithWrongPostType(): void
    {
        $user = User::factory()->admin()->create();
        $feedWithPost = PostType::factory()->create();
        $feedWithPost->posts()->save(Post::factory()->make());
        $feedWithoutPost = PostType::factory()->create();

        $response = $this
            ->actingAsPassportUser($user)
            ->getJson(route('admin.api.v2.feeds.posts.show', [
                $feedWithoutPost->hash_id, $feedWithPost->posts()->first()->hash_id,
            ]));

        $response->assertNotFound();
    }
}
