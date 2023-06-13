<?php

namespace Tests\Unit\Domain\Theming\Shortcodes;

use Ds\Domain\Theming\Shortcodes\SharingLinksShortcode;
use Illuminate\Http\Request;
use Tests\TestCase;
use Thunder\Shortcode\Shortcode\Shortcode;

class SharingLinksShortcodeTest extends TestCase
{
    public function testHandleCurrentUrlInLinks()
    {
        $url = $this->getAppUrl('/blog/testing-url');

        // Mock Laravel Request
        $requestMock = $this->createMock(Request::class);
        $requestMock->expects($this->atLeastOnce())
            ->method('url')
            ->willReturn($url);

        $htmlOutput = $this->sharingLinksHtmlOutput(null, $requestMock);

        $this->assertStringContainsString($this->encodeUrl($url), $htmlOutput);
    }

    public function testHandleExplicitUrlInLinks()
    {
        $url = $this->getAppUrl('/blog/testing-url');
        $htmlOutput = $this->sharingLinksHtmlOutput($url);

        $this->assertStringContainsString($this->encodeUrl($url), $htmlOutput);
    }

    private function encodeUrl(string $url): string
    {
        return 'url=' . urlencode($url) . '"';
    }

    protected function getAppUrl(?string $url = null)
    {
        return config('app.url') . $url;
    }

    private function sharingLinksHtmlOutput(?string $url = null, ?Request $request = null): string
    {
        $shortcode = new Shortcode('sharing_links', $url ? ['url' => url($url)] : [], '');

        $htmlOutput = (new SharingLinksShortcode($request ?: request()))->handle($shortcode);

        $this->assertIsString($htmlOutput);

        return $htmlOutput;
    }
}
