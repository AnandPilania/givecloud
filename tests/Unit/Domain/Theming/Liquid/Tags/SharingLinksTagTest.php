<?php

namespace Tests\Unit\Domain\Theming\Liquid\Tags;

use Tests\TestCase;

class SharingLinksTagTest extends TestCase
{
    public function testNoAttributes(): void
    {
        $this->assertEquals(
            do_shortcode('[sharing_links]'),
            liquid('{% sharing_links %}')
        );
    }

    public function testTitleAttribute(): void
    {
        $this->assertEquals(
            do_shortcode('[sharing_links title="Share my post"]'),
            liquid('{% sharing_links title: "Share my post" %}')
        );
    }

    public function testChannelsAttribute(): void
    {
        $this->assertEquals(
            do_shortcode('[sharing_links title="Share my post" channels="print"]'),
            liquid('{% sharing_links title: "Share my post", channels: channels %}', ['channels' => ['print']])
        );
    }
}
