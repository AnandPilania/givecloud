<?php

namespace Ds\Domain\Theming\Shortcodes;

use Ds\Domain\Theming\Shortcode;
use Ds\Services\SocialLinkService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Liquid\Exception\NotFoundException;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;

class SharingLinksShortcode extends Shortcode
{
    /** @var \Illuminate\Http\Request */
    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Output the sharing links template.
     *
     * @param \Thunder\Shortcode\Shortcode\ShortcodeInterface $s
     * @return string
     */
    public function handle(ShortcodeInterface $s)
    {
        $atts = [
            'url' => (string) $s->getParameter('url', $this->request->url()),
            'title' => (string) $s->getParameter('title', sys_get('clientName')),
            'description' => (string) $s->getParameter('description'),
            'channels' => strtolower((string) $s->getParameter('channels', 'facebook,twitter,email')),
            'hideicon' => Str::boolify((string) $s->getParameter('hideicon', 'no')),
            'hidetext' => Str::boolify((string) $s->getParameter('hidetext', 'no')),
        ];

        try {
            $template = new \Ds\Domain\Theming\Liquid\Template('templates/shortcodes/sharing_links');
        } catch (NotFoundException $e) {
            return '';
        }

        $url = (member() ? member()->getShareableLink($atts['url']) : secure_site_url($atts['url']));
        $available_channels = $this->getChannelsInfo($url, $atts['title'], $atts['description']);

        return $template->render([
            'channels' => array_filter($available_channels, function ($channel) use ($atts) {
                return strpos($atts['channels'], $channel['slug']) !== false;
            }),
            'hideicon' => $atts['hideicon'],
            'hidetext' => $atts['hidetext'],
        ]);
    }

    private function getChannelsInfo($url, $title, $description)
    {
        $sharing_links = SocialLinkService::generate($url, $title, null, $description);

        return [
            [
                'name' => 'Facebook',
                'slug' => 'facebook',
                'url' => $sharing_links['facebook'],
                'icon' => 'fa-facebook',
                'target' => 'gc_impact_share',
            ],
            [
                'name' => 'Twitter',
                'slug' => 'twitter',
                'url' => $sharing_links['twitter'],
                'icon' => 'fa-twitter',
                'target' => 'gc_impact_share',
            ],
            [
                'name' => 'Linkedin',
                'slug' => 'linkedin',
                'url' => $sharing_links['linkedin'],
                'icon' => 'fa-linkedin',
                'target' => 'gc_impact_share',
            ],
            [
                'name' => 'Pinterest',
                'slug' => 'pinterest',
                'url' => $sharing_links['pinterest'],
                'icon' => 'fa-pinterest',
                'target' => 'gc_impact_share',
            ],
            [
                'name' => 'Reddit',
                'slug' => 'reddit',
                'url' => $sharing_links['reddit'],
                'icon' => 'fa-reddit',
                'target' => 'gc_impact_share',
            ],
            [
                'name' => 'Tumblr',
                'slug' => 'tumblr',
                'url' => $sharing_links['tumblr'],
                'icon' => 'fa-tumblr',
                'target' => 'gc_impact_share',
            ],
            [
                'name' => 'Evernote',
                'slug' => 'evernote',
                'url' => $sharing_links['evernote'],
                'icon' => 'fa-paperclip',
                'target' => 'gc_impact_share',
            ],
            [
                'name' => 'WhatsApp',
                'slug' => 'whatsapp',
                'url' => $sharing_links['whatsapp'],
                'icon' => 'fa-whatsapp',
                'target' => 'gc_impact_share',
            ],
            [
                'name' => 'SMS',
                'slug' => 'sms',
                'url' => $sharing_links['sms'],
                'icon' => 'fa-mobile-phone',
            ],
            [
                'name' => 'Email',
                'slug' => 'email',
                'url' => $sharing_links['email'],
                'icon' => 'fa-envelope',
            ],
            [
                'name' => 'Print',
                'slug' => 'print',
                'url' => 'javascript:window.print();',
                'icon' => 'fa-print',
            ],
        ];
    }
}
