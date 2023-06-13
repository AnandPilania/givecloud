<?php

namespace Ds\Domain\Theming\Liquid;

use Ds\Domain\Theming\Theme;
use Ds\Illuminate\Database\MySqlConnection;
use Illuminate\Support\Str;
use Liquid\Context;
use Liquid\Document;
use Liquid\FileSystem;
use Liquid\Liquid;
use Liquid\Template as LiquidTemplate;
use Throwable;

class Template extends LiquidTemplate
{
    /** @var array */
    private static $assets;

    /** @var array */
    private static $shared = [];

    /** @var string */
    private $name;

    /** @var string */
    private $templatePath;

    /** @var \Ds\Domain\Theming\Theme */
    private $themeService;

    /** @var \Liquid\FileSystem */
    private $filesystem;

    /** @var \Liquid\Document */
    private $document;

    /**
     * Create an instance.
     *
     * @param string|null $templatePath
     * @param string|null $rootPath
     * @param \Ds\Domain\Theming\Theme $theme
     * @param string $name
     */
    public function __construct($templatePath = null, $rootPath = '', ?Theme $theme = null, string $name = null)
    {
        if (! self::$assets) {
            self::$assets = [
                'css' => [],
                'js' => [],
                'google_fonts' => [],
            ];
        }

        $this->name = $name ?? $templatePath ?? 'unnamed_liquid_template';
        $this->templatePath = $templatePath;

        if (is_dir($rootPath)) {
            Liquid::set('INCLUDE_PREFIX', '');
            $this->setFileSystem(new LocalFileSystem($rootPath));
        } else {
            $this->setFileSystem(new ThemeFileSystem($rootPath, $theme));
        }

        if ($rootPath !== 'content/') {
            self::registerTag('asset', Tags\AssetTag::class);
            self::registerTag('classes', Tags\ClassesTag::class);
            self::registerTag('form', Tags\FormTag::class);
            self::registerTag('google_font', Tags\GoogleFontTag::class);
            self::registerTag('include', Tags\IncludeTag::class);
            self::registerTag('javascript', Tags\JavascriptTag::class);
            self::registerTag('ray', Tags\RayTag::class);
            self::registerTag('sharing_links', Tags\SharingLinksTag::class);
            self::registerTag('shortcode', Tags\ShortcodeTag::class);
            self::registerTag('stylesheet', Tags\StylesheetTag::class);
        }

        if ($templatePath) {
            if (Str::startsWith($templatePath, 'templates/')) {
                self::registerTag('layout', Tags\LayoutTag::class);
                self::registerTag('localize', Tags\LocalizeTag::class);
                self::registerTag('schema', Tags\SchemaTag::class);
                self::registerTag('section', Tags\SectionTag::class);
            }

            $this->parseFile($templatePath);
        }
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param \Liquid\FileSystem $fileSystem
     * @return void
     */
    public function setFileSystem(FileSystem $fileSystem): void
    {
        $this->filesystem = $fileSystem;

        parent::setFileSystem($fileSystem);

        if ($fileSystem instanceof ThemeFileSystem) {
            $this->themeService = $fileSystem->getTheme();
        } else {
            $this->themeService = app('theme');
        }
    }

    /**
     * Add a piece of shared data to the environment.
     *
     * @param array|string $key
     * @param mixed|null $value
     * @return mixed
     */
    public static function share($key, $value = null)
    {
        $keys = is_array($key) ? $key : [$key => $value];

        foreach ($keys as $key => $value) {
            static::$shared[$key] = $value;
        }

        return $value;
    }

    /**
     * @return \Liquid\Document
     */
    public function getRoot()
    {
        return $this->document;
    }

    /**
     * Parses the given source string
     *
     * @param string $source
     * @return Template
     */
    public function parse($source)
    {
        if (! self::getCache()) {
            return $this->customParseAlways($source);
        }

        $hash = md5($source);
        $this->document = self::getCache()->read($hash);

        // if no cached version exists, or if it checks for includes
        if ($this->document == false || $this->document->hasIncludes() == true) {
            $this->customParseAlways($source);
            self::getCache()->write($hash, $this->document);
        }

        return $this;
    }

    /**
     * Parses the given source string regardless of caching
     *
     * @param string $source
     * @return Template
     */
    private function customParseAlways($source)
    {
        $tokens = self::tokenize($source);
        $this->document = new Document($tokens, $this->filesystem);

        return $this;
    }

    /**
     * Render template.
     *
     * @param string $liquid
     * @param array $assigns
     * @param string $name
     * @return string
     */
    public static function renderLiquid(string $liquid, array $assigns = [], string $name = null): string
    {
        // shortcircuit rendering if there's nothing to render
        if (trim($liquid) === '') {
            return '';
        }

        $template = new self(null, '', null, $name);
        $template->parse($liquid);

        return $template->render($assigns);
    }

    /**
     * Render template.
     *
     * @param array $assigns
     * @param array $filters
     * @param array $registers
     * @return string
     */
    public function render(array $assigns = [], $filters = null, array $registers = [])
    {
        $assigns = array_merge(static::$shared, $assigns);

        // Since Drop access while rendering should always be contextualized within
        // a 1-way dataflow we will cache all SELECT statements made during rendering
        MySqlConnection::cacheSelects(true);

        $layoutName = '';

        $context = new Context(Drop::resolveData([
            'cart' => Drop::factory(cart(), 'Cart'),
            'account' => Drop::factory(member(), 'Account'),
            'site' => Drop::factory(null, 'Site'),
            'page_title' => $assigns['pageTitle'] ?? $assigns['page']->title ?? null,
            'categories' => Drop::factory(null, 'Categories'),
            'linklists' => Drop::factory(null, 'LinkLists'),
            'pages' => Drop::factory(null, 'Pages'),
            'posts' => Drop::factory(null, 'Posts'),
            'settings' => Drop::factory(null, 'Settings'),
            'request' => new Drops\RequestDrop,
            'theme' => new Drops\ThemeDrop($this->themeService->getEloquentTheme()),
            'error' => session('error'),
            'errors' => session('errors', []),
        ], false));

        $context->merge(Drop::resolveData($assigns, false));

        $context->registers['assets'] = &self::$assets;

        $context->registers['javascript'] = '';
        $context->registers['stylesheet'] = '';
        $context->registers['localizations'] = [];
        $context->registers['content_for_header_rendered'] = false;

        if (Str::startsWith($this->templatePath, 'templates/') && ! Str::startsWith($this->templatePath, 'templates/shortcodes/')) {
            $layoutName = 'theme';

            $templateDrop = new Drops\TemplateDrop($this->templatePath);
            $context->merge(['template' => $templateDrop]);

            $bodyClasses = [
                $templateDrop->class_name(),
                strtolower('currency-' . $context->get('cart.currency.iso_code')),
            ];

            if (Str::startsWith($this->templatePath, 'templates/collection')) {
                $bodyClasses[] = 'collection-' . $context->get('category')->id;
            } elseif (Str::startsWith($this->templatePath, 'templates/page')) {
                $bodyClasses[] = 'page-' . $context->get('page')->id;
            } elseif (Str::startsWith($this->templatePath, 'templates/post-type')) {
                $bodyClasses[] = 'post-type-' . $context->get('post_type')->id;
            } elseif (Str::startsWith($this->templatePath, 'templates/post')) {
                $bodyClasses[] = 'post-' . $context->get('post')->id;
            } elseif (Str::startsWith($this->templatePath, 'templates/product')) {
                $bodyClasses[] = 'product-' . $context->get('product')->id;
            } elseif ($this->templatePath === 'templates/fundraiser') {
                $bodyClasses[] = 'fundraiser-' . $context->get('fundraising_page')->id;
            } elseif ($this->templatePath === 'templates/sponsorship') {
                $bodyClasses[] = 'sponsorship-' . $context->get('sponsorship')->id;
            }

            $context->set('body_classes', implode(' ', $bodyClasses));
        }

        // we don't want access to any environment variables
        // from our Liquid templates
        $context->environments = [];

        $context->addFilters(new Filters\ArrayFilters($this->themeService));
        $context->addFilters(new Filters\ColorFilters($this->themeService));
        $context->addFilters(new Filters\HTMLFilters($this->themeService));
        $context->addFilters(new Filters\LocaleFilters($this->themeService));
        $context->addFilters(new Filters\MathFilters($this->themeService));
        $context->addFilters(new Filters\MoneyFilters($this->themeService));
        $context->addFilters(new Filters\StringFilters($this->themeService));
        $context->addFilters(new Filters\URLFilters($this->themeService));
        $context->addFilters(new Filters\AdditionalFilters($this->themeService));

        foreach ($this->document->getNodelist() as $token) {
            if ($token instanceof Tags\LayoutTag) {
                $layoutName = $token->getLayoutName();
            }
        }

        if (empty($layoutName)) {
            $context->merge([
                'content_for_header' => new Drops\ContentForHeaderDrop($context),
                'content_for_footer' => new Drops\ContentForFooterDrop($context),
            ]);

            $output = $this->document->render($context);

            MySqlConnection::cacheSelects(false);

            return $output;
        }

        if (isGivecloudExpress()) {
            $layoutName = 'theme.gcx';
        }

        $source = $this->filesystem->readTemplateFile("layout/$layoutName");

        $tokens = self::tokenize($source);
        $layout = new Document($tokens, $this->filesystem);

        $context->merge([
            'content_for_header' => new Drops\ContentForHeaderDrop($context),
            'content_for_layout' => new Drops\ContentForLayoutDrop($context, $this),
            'content_for_footer' => new Drops\ContentForFooterDrop($context),
        ]);

        try {
            $output = $layout->render($context);
        } catch (Throwable $e) {
            report($e);
        }

        MySqlConnection::cacheSelects(false);

        return $output ?? '';
    }
}
