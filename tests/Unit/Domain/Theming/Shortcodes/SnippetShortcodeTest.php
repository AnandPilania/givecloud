<?php

namespace Tests\Unit\Domain\Theming\Shortcodes;

use Ds\Models\Post;
use Illuminate\Support\Str;
use Tests\TestCase;

class SnippetShortcodeTest extends TestCase
{
    public function testRecoveryOfRecursivelyNestedShortcode()
    {
        $post = Post::factory()->snippet()->create();
        $post->body = "Why would a '[snippet id={$post->id}]' do you like this?";
        $post->save();

        $this->assertSame("Why would a '' do you like this?", do_shortcode("[snippet id={$post->id}]"));
    }

    public function testRenderingSnippetWithoutIdOrName()
    {
        $this->assertSame(do_shortcode('[snippet]'), '');
    }

    public function testRenderingSnippetByIdWithParameterAndContent()
    {
        $post = Post::factory()->snippet()->create(['body' => '<h3>[[title]]</h3> <p>[[content]]</p>']);

        $this->assertSame(
            do_shortcode("[snippet id={$post->id} title=Yoda]Do or do not, there is no try.[/snippet]"),
            '<h3>Yoda</h3> <p>Do or do not, there is no try.</p>'
        );
    }

    public function testRenderingSnippetByNameWithParameterAndNoContent()
    {
        $post = Post::factory()->snippet()->create([
            'name' => Str::random(60),
            'body' => '<h3>[[title]]</h3> <p>[[content]]</p>',
        ]);

        $this->assertSame(
            do_shortcode("[snippet name=\"{$post->name}\" title=Yoda]"),
            '<h3>Yoda</h3> <p>[[content]]</p>'
        );
    }

    public function testRenderingSnippetContainingShortcodes()
    {
        $post1 = Post::factory()->snippet()->create(['body' => 'Kermit the Frog']);
        $post2 = Post::factory()->snippet()->create(['body' => "<h3>[[title]]</h3> [snippet id=$post1->id]"]);

        $this->assertSame(
            do_shortcode("[snippet id={$post2->id} title=Yoda]"),
            "<h3>Yoda</h3> {$post1->body}"
        );
    }
}
