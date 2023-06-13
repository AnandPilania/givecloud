<?php

namespace Tests\Unit\Domain\Theming\Shortcodes;

use Ds\Models\Category;
use Ds\Models\Post;
use Ds\Models\PostType;
use Tests\TestCase;

class PostsShortcodeTest extends TestCase
{
    public function testScopingToCategories()
    {
        $postType = PostType::factory()
            ->has(Post::factory()->count(3))
            ->create();

        $category = Category::factory()->create([
            'assignable_id' => $postType->getKey(),
            'assignable_type' => 'post_type',
        ]);

        $postType->posts[0]->categories()->sync([$category->getKey()]);

        $content = do_shortcode(sprintf(
            '[posts type="%d" style="list" categories="%d"]',
            $postType->getKey(),
            $category->getKey()
        ));

        $format = '<h5 class="text-primary">%s</h5>';

        $this->assertStringContainsString(sprintf($format, $postType->posts[0]->name), $content);
        $this->assertStringNotContainsString(sprintf($format, $postType->posts[1]->name), $content);
        $this->assertStringNotContainsString(sprintf($format, $postType->posts[2]->name), $content);
    }
}
