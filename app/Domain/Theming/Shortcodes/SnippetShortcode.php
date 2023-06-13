<?php

namespace Ds\Domain\Theming\Shortcodes;

use Ds\Domain\Theming\Liquid\Drop;
use Ds\Domain\Theming\Shortcode;
use Ds\Repositories\SnippetRepository;
use Illuminate\Support\Arr;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;

class SnippetShortcode extends Shortcode
{
    /** @var string */
    protected $invokeKey = 'id';

    /**
     * Output the posts template.
     *
     * @param \Thunder\Shortcode\Shortcode\ShortcodeInterface $s
     * @return string
     */
    public function handle(ShortcodeInterface $s)
    {
        $id = $s->getParameter('id', '');
        $name = $s->getParameter('name', '');

        $post = empty($id)
            ? app(SnippetRepository::class)->findByName($name)
            : app(SnippetRepository::class)->find($id);

        if (empty($post)) {
            return '';
        }

        $data = [
            'id' => uniqid('snippet-shortcode-'),
            'post' => $post,
            'posts' => Drop::factory(null, 'Posts'),
            'content' => $s->getContent(),
            'parameters' => Arr::except($s->getParameters(), ['id', 'name', 'style']),
        ];

        $mergeTags = $data['parameters'];

        if ($data['content']) {
            $mergeTags = array_merge(['content' => $data['content']], $mergeTags);
        }

        $body = string_substituteFromArray(do_shortcode($post->body), $mergeTags);

        if ($post->misc1 === 'liquid') {
            return liquid($body, $data, "snippet_shortcode:{$post->id}");
        }

        return $body;
    }
}
