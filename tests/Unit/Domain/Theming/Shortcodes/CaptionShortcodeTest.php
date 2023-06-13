<?php

namespace Tests\Unit\Domain\Theming\Shortcodes;

use Tests\TestCase;

class CaptionShortcodeTest extends TestCase
{
    public function testImgAndCaptionInContent()
    {
        $content = do_shortcode(<<<'HTML'
            [caption id="attachment_45441" align="alignleft" width="450"]
                <img src="image.jpg"> Aliquam felis sapien, cursus non.
            [/caption]
            HTML);

        $this->assertEquals(trim($content), trim(<<<'HTML'
            <div id="attachment-45441" class="gc-caption alignleft" style="width: 450px">
                <img src="image.jpg">
                <p id="caption-attachment-45441" class="gc-caption-text">Aliquam felis sapien, cursus non.</p>
            </div>
            HTML));
    }

    public function testEmptyCaption()
    {
        $content = do_shortcode(<<<'HTML'
            [caption id="attachment_45441" align="alignleft" width="450"]
                <img src="image.jpg">
            [/caption]
            HTML);

        $this->assertEquals(trim($content), '<img src="image.jpg">');
    }
}
