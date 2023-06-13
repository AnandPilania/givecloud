<?php

namespace Ds\Domain\Theming\Shortcodes;

use Ds\Domain\Theming\Liquid\Drop;
use Ds\Domain\Theming\Shortcode;
use Illuminate\Support\Arr;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;

class PostShortcode extends Shortcode
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
        $style = $s->getParameter('style', 'none');

        if (empty($id) && empty($name)) {
            return $this->error("Either the 'id' or 'name' attribute is required");
        }

        if ($id) {
            $post = \Ds\Models\Post::find($id);
        } else {
            $post = \Ds\Models\Post::where('name', $name)->first();
        }

        if (empty($post)) {
            return '';
        }

        $data = [
            'id' => uniqid('post-shortcode-'),
            'post' => $post,
            'posts' => Drop::factory(null, 'Posts'),
            'parameters' => Arr::except($s->getParameters(), ['id', 'name', 'style']),
        ];

        if ($post->misc1 === 'liquid') {
            return liquid($post->body, $data, "post_shortcode:{$post->id}");
        }

        $template = rtrim("templates/shortcodes/post.$style", '.');
        $template = new \Ds\Domain\Theming\Liquid\Template($template);

        return $template->render($data);
    }
}
