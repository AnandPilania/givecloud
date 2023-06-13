<?php

namespace Tests\Unit\Domain\Theming\Liquid\Tags;

use Tests\TestCase;

class ShortcodeTagTest extends TestCase
{
    public function testNoAttributes(): void
    {
        $this->assertEquals(
            do_shortcode('[sharing_links]'),
            liquid('{% shortcode "sharing_links" %}')
        );
    }

    public function testWithAttributes(): void
    {
        $this->assertEquals(
            do_shortcode('[sharing_links title="Share my post" channels="print"]'),
            liquid('{% shortcode "sharing_links", title: "Share my post", channels: channels %}', ['channels' => ['print']])
        );
    }
}
