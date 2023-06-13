<?php

namespace Tests\Unit\Domain\Theming;

use Ds\Domain\Theming\ScssCompiler;
use Ds\Models\Asset;
use Ds\Models\Theme;
use Tests\TestCase;

class ScssCompilerTest extends TestCase
{
    public function testImportFromTheming(): void
    {
        $this->assertStringContainsString(
            '.bootstrap-mixins-included',
            app('scss')->compile(<<<'EOT'
                @import '~bootstrap4/functions';
                @import '~bootstrap4/variables';
                @import '~bootstrap4/mixins/breakpoints';
                @include media-breakpoint-up(md) {
                    .bootstrap-mixins-included {
                        min-height: 600px;
                    }
                }
            EOT)
        );
    }

    public function testImportFromThemingAndLockedThemeStyles(): void
    {
        $this->assertStringContainsString(
            ".bootstrap-mixins-included {\n    background-color: #fff;\n  }",
            app('scss')->compile(<<<'EOT'
                @import 'settings/variables';
                @import '~bootstrap4/functions';
                @import '~bootstrap4/variables';
                @import '~bootstrap4/mixins/breakpoints';
                @include media-breakpoint-up(md) {
                    .bootstrap-mixins-included {
                        background-color: $body-bg;
                    }
                }
            EOT)
        );
    }

    public function testImportFromThemingAndUnlockedThemeStyles(): void
    {
        $theme = Theme::factory()->unlocked()->create();

        Asset::factory()->style()->create([
            'theme_id' => $theme->getKey(),
            'key' => 'styles/settings/variables.scss',
            'value' => '$body-bg: #013370;',
        ]);

        $compiler = new ScssCompiler($theme);
        $this->assertStringContainsString(
            ".bootstrap-mixins-included {\n    background-color: #013370;\n  }",
            $compiler->compile(<<<'EOT'
                @import 'settings/variables';
                @import '~bootstrap4/functions';
                @import '~bootstrap4/variables';
                @import '~bootstrap4/mixins/breakpoints';
                @include media-breakpoint-up(md) {
                    .bootstrap-mixins-included {
                        background-color: $body-bg;
                    }
                }
            EOT)
        );
    }
}
