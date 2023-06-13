<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Theming\Liquid\Drop;
use Illuminate\Support\Arr;
use Liquid\Context;

class ContentForHeaderDrop extends Drop
{
    const SOURCE_REQUIRED = false;

    /** @var \Liquid\Context */
    protected $context;

    /**
     * Create an instance.
     *
     * @param \Liquid\Context $context
     */
    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * Output content for the header.
     *
     * @return string
     */
    public function __toString()
    {
        $settings = $this->context->get('settings');

        $output = (string) view('gc_header', [
            'logrocket_template_name' => $this->getLogRocketTemplateName(),
        ]);

        $this->context->registers['content_for_header_rendered'] = true;

        if (count($this->context->registers['assets']['google_fonts'])) {
            $fonts = implode('|', Arr::pluck($this->context->registers['assets']['google_fonts'], 'param'));
            $this->context->registers['assets']['css'] = Arr::prepend(
                $this->context->registers['assets']['css'],
                [
                    'url' => "https://fonts.googleapis.com/css?family=$fonts",
                    'footer' => false,
                ]
            );
        }

        collect($this->context->registers['assets']['css'])
            ->where('combine', true)
            ->unique('url')
            ->where('footer', false)
            ->pluck('url')
            ->chunk(12)
            ->each(function ($combine) use (&$output) {
                $combine = $combine->implode(',');
                $output .= "<link rel=\"stylesheet\" href=\"https://cdn.givecloud.co/combine/$combine\">\n";
            });

        collect($this->context->registers['assets']['css'])
            ->where('combine', false)
            ->unique('url')
            ->where('footer', false)
            ->each(function ($data) use (&$output) {
                $output .= "<link rel=\"stylesheet\" href=\"{$data['url']}\">\n";
            });

        collect($this->context->registers['assets']['js'])
            ->where('combine', true)
            ->unique('url')
            ->where('footer', false)
            ->pluck('url')
            ->chunk(12)
            ->each(function ($combine) use (&$output) {
                $combine = $combine->implode(',');
                $output .= "<script type=\"text/javascript\" src=\"https://cdn.givecloud.co/combine/$combine\"></script>\n";
            });

        collect($this->context->registers['assets']['js'])
            ->where('combine', false)
            ->unique('url')
            ->where('footer', false)
            ->each(function ($data) use (&$output) {
                $options = '';
                if (Arr::get($data, 'async')) {
                    $options .= ' async';
                }
                if (Arr::get($data, 'defer')) {
                    $options .= ' defer';
                }
                $output .= "<script type=\"{$data['type']}\" src=\"{$data['url']}\"$options></script>\n";
            });

        $this->context->registers['assets']['js'] = collect($this->context->registers['assets']['js'])
            ->where('footer', true)
            ->all();

        if ($this->context->registers['stylesheet']) {
            $output .= "<style>\n{$this->context->registers['stylesheet']}\n</style>\n";
        }

        if (! $this->context->get('exclude_custom_head_tags') && $settings['custom-head-tags']) {
            $output .= "{$settings['custom-head-tags']}\n";
        }

        return rtrim($output);
    }

    private function getLogRocketTemplateName(): ?string
    {
        $templateMapping = [
            'fundraiser' => 'fundraiser',
            'page.multi-item-checkout' => 'multi-fund',
            'page.split-fund-donations' => 'split-fund',
            'product.page-with-payment' => 'page-with-payment',
        ];

        return $templateMapping[$this->context->get('template.name')] ?? null;
    }
}
