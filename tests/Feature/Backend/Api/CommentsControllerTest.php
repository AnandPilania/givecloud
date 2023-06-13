<?php

namespace Tests\Feature\Backend\Api;

use Ds\Models\Comment;
use Tests\TestCase;

class CommentsControllerTest extends TestCase
{
    public function testUserCanEditOwnComment(): void
    {
        $user = $this->createUserWithPermissions(['member.add']);

        $comment = Comment::factory()->by($user)->create();

        $newComment = 'This is the updated body';

        $this
            ->actingAsPassportUser($user)
            ->postJson(route('comments.update', $comment), ['body' => $newComment])
            ->assertJsonPath('data.body', $newComment);
    }

    public function testUserCanDeleteOwnComment(): void
    {
        $user = $this->createUserWithPermissions(['member.add']);

        $comment = Comment::factory()->by($user)->create();

        $this->actingAsPassportUser($user)
            ->deleteJson(route('comments.destroy', $comment))
            ->assertStatus(204);
    }

    public function testUserCannotDeleteOthersComment(): void
    {
        $user = $this->createUserWithPermissions(['member.add']);

        $comment = Comment::factory()->by($user)->create();

        // Change user
        $otherUser = $this->createUserWithPermissions(['member.add']);

        $this
            ->actingAsPassportUser($otherUser)
            ->deleteJson(route('comments.destroy', $comment))
            ->assertStatus(403);
    }

    public function testSuperUserCanDeleteOthersComment(): void
    {
        $user = $this->createUserWithPermissions(['member.add']);

        $comment = Comment::factory()->by($user)->create();

        // Change user
        $accountAdminUser = $this->createUserWithPermissions(['member.'], ['is_account_admin' => 1]);

        $this
            ->actingAsPassportUser($accountAdminUser)
            ->deleteJson(route('comments.destroy', $comment))
            ->assertStatus(204);
    }

    public function testUserCannotEditOthersComments(): void
    {
        $user = $this->createUserWithPermissions(['member.add']);

        $comment = Comment::factory()->by($user)->create();

        // Change user.
        $otherUser = $this->createUserWithPermissions(['member.add']);

        $this
            ->actingAsPassportUser($otherUser)
            ->postJson(route('comments.update', $comment), ['body' => 'This should not be updated'])
            ->assertStatus(403);
    }
}
