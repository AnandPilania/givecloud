<?php

namespace Ds\Services;

use Illuminate\Support\Str;
use SocialLinks\Page;

class SocialLinkService
{
    /**
     * create social links from a url
     *
     * @param string $url
     * @param string $title
     * @param string|null $thumbnail
     * @param string|null $description
     * @return array
     */
    public static function generate($url, $title, $thumbnail = null, $description = null)
    {
        $page = new Page([
            'url' => $url,
            'title' => $title ? substr($title, 0, 150) : null,
            'text' => $description ? substr($description, 0, 300) : null,
            'image' => $thumbnail,
        ]);

        return [
            'evernote' => $page->evernote->shareUrl,
            'facebook' => $page->facebook->shareUrl,
            'email' => self::generateEmailLink($url, $title, $description),
            'linkedin' => $page->linkedin->shareUrl,
            'pinterest' => $page->pinterest->shareUrl,
            'reddit' => $page->reddit->shareUrl,
            'sms' => $page->sms->shareUrl,
            'tumblr' => $page->tumblr->shareUrl,
            'whatsapp' => $page->whatsapp->shareUrl,
            'twitter' => $page->twitter->shareUrl,
        ];
    }

    protected static function generateEmailLink($url, $title, $text): string
    {
        $text = Str::limit($text, 290);

        $text = strip_tags("{$text}\n\nRead More: {$url}");
        return "mailto:?subject=" . rawurlencode($title) . "&body=" . rawurlencode($text);
    }
}
