<?php

namespace Ds\Domain\Theming\Liquid\Drops;

use Ds\Domain\Commerce\Contracts\Viewable;
use Ds\Domain\Theming\Liquid\Drop;
use Ds\Services\GivecloudCoreConfigRepository;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Liquid\Context;

class ContentForFooterDrop extends Drop
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
        $output = "\n";
        $settings = $this->context->get('settings');

        $configRepo = app(GivecloudCoreConfigRepository::class)->setContext($this->context);

        $config = $configRepo->getConfig();
        $gateways = $configRepo->getGateways();

        $output .= sprintf("<script src=\"%s\"></script>\n", $config['script_src']);
        $output .= sprintf("<script>\nGivecloud.setConfig(%s);\n</script>\n", json_encode($config));

        $gatewayConfigured = [];
        foreach ($gateways as $provider) {
            if ($provider && $provider->gateway instanceof Viewable) {
                if (! array_key_exists($provider->id, $gatewayConfigured)) {
                    $output .= $provider->gateway->getView() . PHP_EOL;
                    $gatewayConfigured[$provider->id] = true;
                }
            }
        }

        collect($this->context->registers['assets']['css'])
            ->where('combine', true)
            ->unique('url')
            ->where('footer', true)
            ->pluck('url')
            ->chunk(12)
            ->each(function ($combine) use (&$output) {
                $combine = $combine->implode(',');
                $output .= "<link rel=\"stylesheet\" href=\"https://cdn.givecloud.co/combine/$combine\">\n";
            });

        collect($this->context->registers['assets']['css'])
            ->where('combine', false)
            ->unique('url')
            ->where('footer', true)
            ->each(function ($data) use (&$output) {
                $output .= "<link rel=\"stylesheet\" href=\"{$data['url']}\">\n";
            });

        if (count($this->context->registers['localizations'])) {
            $output .= sprintf(
                "<script>\nwindow.themeLocalizationMap = %s;\n</script>\n\n",
                json_encode($this->context->registers['localizations'], JSON_PRETTY_PRINT)
            );
        }

        collect($this->context->registers['assets']['js'])
            ->where('combine', true)
            ->unique('url')
            ->where('footer', true)
            ->pluck('url')
            ->chunk(12)
            ->each(function ($combine) use (&$output) {
                $combine = $combine->implode(',');
                $output .= "<script type=\"text/javascript\" src=\"https://cdn.givecloud.co/combine/$combine\"></script>\n";
            });

        collect($this->context->registers['assets']['js'])
            ->where('combine', false)
            ->reverse()
            ->unique('url')
            ->reverse()
            ->where('footer', true)
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

        if ($this->context->registers['javascript']) {
            $output .= "<script>\n{$this->context->registers['javascript']}\n</script>\n";
        }

        if (! $this->context->get('exclude_custom_scripts') && $settings['custom-scripts']) {
            $output .= "{$settings['custom-scripts']}\n";
        }

        $output .= (string) view('gc_footer', [
            'show_branding' => $this->context->get('show_branding') ?? $this->context->get('site.show_branding'),
            'has_active_admin_login' => Auth::check() && ! $this->context->get('hide_admin_action_panel'),
            'has_site_design_permission' => user()->can('customize.edit'),
            'fundraising_page' => user()->can('fundraisingpages') ? $this->context->get('fundraising_page') : null,
            'page' => user()->can('node') ? $this->context->get('page') : null,
            'post' => user()->can('post') ? $this->context->get('post') : null,
            'post_type' => user()->can('posttype') ? $this->context->get('post_type') : null,
            'product' => user()->can('product') ? $this->context->get('product') : null,
            'sponsorship' => user()->can('sponsorship') ? $this->context->get('sponsorship') : null,
        ]);

        return rtrim($output);
    }
}
