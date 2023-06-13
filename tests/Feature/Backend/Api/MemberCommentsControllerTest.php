<?php

namespace Tests\Feature\Backend\Api;

use Ds\Models\Comment;
use Ds\Models\Member;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;

class MemberCommentsControllerTest extends TestCase
{
    /*
     * Using Migrations instead of RefreshDatabase trait because Full-Text search
     * won't work in a transaction mode.
     * @see https://dev.mysql.com/doc/refman/8.0/en/innodb-fulltext-index.html#innodb-fulltext-index-transaction
     * @see https://laracasts.com/discuss/channels/testing/issue-with-data-persistenceeloquent-query-when-running-tests?page=1#reply=475376
    */
    use DatabaseMigrations;

    protected bool $refreshDatabase = false;

    public function testUserCanListCommentsOfAccount(): void
    {
        $user = $this->createUserWithPermissions(['member.add']);

        $member = Member::factory()->create();

        $this
            ->actingAsPassportUser($user)
            ->getJson(route('member.comments.index', $member))
            ->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }

    public function testUserCanAddCommentToAccountAndEndpointReturnsIt(): void
    {
        $member = Member::factory()->create();
        $comment = 'This is a test';

        $user = $this->createUserWithPermissions(['member.add']);

        $this
            ->actingAsPassportUser($user)
            ->postJson(route('member.comments.store', $member), ['body' => $comment])
            ->assertStatus(201)
            ->assertJsonPath('data.body', $comment);

        $this
            ->getJson(route('member.comments.index', $member))
            ->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function testPageOverflowReturnsLastPage(): void
    {
        $user = $this->createUserWithPermissions(['member.add']);
        $this->actingAs($user);

        $member = Member::factory()->create();

        Comment::factory()
            ->count(30)
            ->by($user)
            ->for($member, 'commentable')
            ->create();

        $this
            ->actingAsPassportUser($user)
            ->getJson(route('member.comments.index', [
                'member' => $member->getKey(),
                'page' => 7, // Force overflow
            ]))
            ->assertStatus(200)
            ->assertJson(function (AssertableJson $json) {
                $json->where('meta.current_page', 2)
                    ->where('meta.last_page', 2)
                    ->etc();
            });
    }

    public function testSearchReturnsResults(): void
    {
        $user = $this->createUserWithPermissions(['member.add']);
        $this->actingAs($user);

        $member = Member::factory()->create();

        Comment::factory()
            ->count(12)
            ->by($user)
            ->for($member, 'commentable')
            ->create();

        Comment::factory()
            ->count(2)
            ->for($member, 'commentable')
            ->by($user)
            ->create([
                'body' => 'This is the test string that should be returned. Somewordtosearchfor. Another sentence',
            ]);

        $this
            ->actingAsPassportUser($user)
            ->getJson(route('member.comments.index', [
                'member' => $member->getKey(),
                'filter[body]' => 'Somewordtosearchfor',
            ]))
            ->assertStatus(200)
            ->assertJsonCount(2, 'data');
    }
}
