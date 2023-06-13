<?php

namespace Ds\Domain\Theming;

use Carbon\Carbon;
use Ds\Models\Theme as EloquentTheme;
use Illuminate\Contracts\Config\Repository as Config;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class Theme
{
    /** @var \Illuminate\Contracts\Config\Repository */
    protected $config;

    /** @var \Ds\Models\Theme */
    protected $theme;

    /** @var \Ds\Domain\Theming\Metadata */
    protected $metadata;

    /**
     * Create an instance.
     *
     * @param \Ds\Models\Theme $theme
     */
    public function __construct(Config $config, EloquentTheme $theme)
    {
        $this->config = $config;
        $this->theme = $theme;
        $this->metadata = new Metadata($this);
    }

    /**
     * Get the theme for a given handle.
     *
     * @param string|null $handle
     * @return \Ds\Domain\Theming\Theme
     */
    public function theme(?string $handle = null)
    {
        if (empty($handle)) {
            return $this;
        }

        return reqcache("theming-theme:$handle", function () use ($handle) {
            if ($handle === 'givecloud') {
                $theme = new EloquentTheme;
                $theme->handle = 'givecloud';
                $theme->locked = true;
            } else {
                $theme = EloquentTheme::where('handle', $handle)->firstOrFail();
            }

            return new self($this->config, $theme);
        });
    }

    /**
     * Get the Eloquent model for the theme.
     *
     * @return \Ds\Models\Theme
     */
    public function getEloquentTheme(): EloquentTheme
    {
        return $this->theme;
    }

    /**
     * Resolve an asset for a given key.
     *
     * @param string $key
     * @return \Ds\Models\Asset
     */
    public function asset($key)
    {
        if ($this->theme->locked || preg_match('#(assets|content/previews)/#', $key)) {
            $filePath = base_path("resources/themes/{$this->theme->handle}/$key");

            if (! file_exists($filePath)) {
                throw new ModelNotFoundException("Asset [$key] not found.");
            }

            $asset = new \Ds\Models\Asset;
            $asset->theme_id = $this->theme->id;
            $asset->key = $key;
            $asset->value = file_get_contents($filePath);
            $asset->created_at = Carbon::createFromTimeStamp(filemtime($filePath));
            $asset->updated_at = $asset->created_at->copy();
            $asset->setThemeService($this);

            if (preg_match('#(assets|content/previews)/#', $key)) {
                $asset->public_url = secure_site_url("/static/{$this->theme->handle}/{$asset->key}");
            }

            return $asset;
        }

        $asset = $this->theme->assets()
            ->where('key', $key)
            ->first();

        if (! $asset) {
            throw new ModelNotFoundException("Asset [$key] not found.");
        }

        return $asset->setThemeService($this);
    }

    /**
     * Retrieve list of assets.
     *
     * @param string $pattern
     * @return \Illuminate\Support\Collection
     */
    public function getAssetList($pattern = '*')
    {
        if ($this->theme->locked) {
            $themePath = base_path("resources/themes/{$this->theme->handle}/");

            $keys = collect(File::allFiles($themePath))
                ->map(function ($file) use ($themePath) {
                    return substr($file->getPathname(), strlen($themePath));
                });
        } else {
            $keys = $this->theme->assets()->pluck('key');
        }

        return $keys->filter(function ($key) use ($pattern) {
            return fnmatch($pattern, $key);
        });
    }

    /**
     * Retrieve content templates.
     *
     * @return array
     */
    public function getContentTemplates()
    {
        $templates = [[
            'type' => 'header',
            'title' => 'Default',
            'templates' => [],
        ]];

        try {
            $content = $this->asset('config/content.json');
            $content = json_decode($content->value ?? null);
        } catch (\Throwable $e) {
            return [];
        }

        collect($content)->each(function ($item) use (&$templates) {
            if (empty($item->type) || empty($item->title)) {
                return;
            }
            if ($item->type === 'snippet') {
                $template = [
                    'type' => $item->type,
                    'handle' => Str::slug(pathinfo($item->content, PATHINFO_FILENAME)),
                    'title' => $item->title,
                    'content' => secure_site_url("/static/{$this->theme->handle}/content/{$item->content}?v=" . Str::random(10)),
                ];
                if (isset($item->preview)) {
                    if ($this->theme->locked) {
                        $template['preview'] = secure_site_url("/-/static/{$this->theme->handle}/content/previews/{$item->preview}");
                    } else {
                        $template['preview'] = secure_site_url("/static/{$this->theme->handle}/content/previews/{$item->preview}");
                    }
                } else {
                    $template['preview'] = null;
                }
                $templates[count($templates) - 1]['templates'][] = $template;
            } elseif ($item->type === 'header') {
                $templates[] = [
                    'type' => $item->type,
                    'title' => $item->title,
                    'templates' => [],
                ];
            }
        });

        return array_values(array_filter($templates, function ($item) {
            return count($item['templates']);
        }));
    }

    /**
     * Get or set a setting.
     *
     * @param string|array|null $name
     * @return mixed
     */
    public function setting($name = null)
    {
        static $cache;

        if ($cache === null) {
            $cache = collect();

            $values = DB::table('settings')
                ->whereNull('theme_id')
                ->orWhere('theme_id', $this->theme->id)
                ->get()
                ->keyBy('name');

            $asset = $this->asset('config/settings_schema.json');
            collect(json_decode($asset->value))
                ->pluck('settings')
                ->flatten()
                ->reject(function ($item) {
                    return empty($item->name);
                })->each(function ($item) use ($values, $cache) {
                    $item->theme_id = $this->theme->id;
                    $item->value = data_get($values, "{$item->name}.value", $item->default ?? null);
                    if (isset($item->allow_blank) && ! $item->allow_blank && (string) ($item->value) === '') {
                        $item->value = $item->default;
                    }
                    $item->editable = false;
                    if (is_string($item->value) && in_array($item->type, ['multi', 'multi-custom'])) {
                        $item->value = json_decode($item->value);
                    }
                    $cache[$item->name] = $item;
                });

            $values->each(function ($item) use ($cache) {
                if ($item->theme_id === null) {
                    $cache[$item->name] = (object) [
                        'theme_id' => null,
                        'name' => $item->name,
                        'value' => $item->value,
                        'type' => $item->type,
                        'label' => $item->label,
                        'info' => $item->info,
                        'editable' => true,
                    ];
                }
            });
        }

        if ($name === null) {
            return $cache;
        }

        if (is_array($name)) {
            collect($name)->each(function ($value, $name) use ($cache) {
                if (isset($cache[$name])) {
                    $item = \Ds\Models\Setting::firstOrNew([
                        'name' => $name,
                        'theme_id' => $cache[$name]->theme_id,
                    ]);
                    if (is_array($value)) {
                        $item->value = json_encode($value);
                    } else {
                        $item->value = $value;
                    }
                    $item->save();
                }
            });
            $cache = null;
            Cache::tags('theming')->flush();
            Cache::tags('theming')->forever('settings_updated', now()->toApiFormat());

            return null;
        }

        // slugify to allow for legacy calls with are using
        // the label for the setting instead of the name
        $name = Str::slug($name, '_');

        return $cache[$name] ?? null;
    }

    /**
     * Checks if translation exists.
     *
     * @param string $key
     * @return bool
     */
    public function hasTranslation(string $key)
    {
        return (bool) Arr::has($this->getTranslationKeys(), $key);
    }

    /**
     * Returns the corresponding string of translated text from the locale file.
     *
     * @param string $key
     * @param array $data
     * @return mixed
     */
    public function translate(string $key, array $data = [])
    {
        $value = Arr::get($this->getTranslationKeys(), $key, $key);

        // Check for pluralization in translation keys
        if (Str::endsWith($key, '_count')) {
            $data['count'] = is_countable($data['count'] ?? null)
                ? count($data['count'])
                : $data['count'] ?? 0;

            if ($data['count'] === 0 && ! empty($value['zero'])) {
                $value = $value['zero'];
            } elseif ($data['count'] < 2 && ! empty($value['one'])) {
                $value = $value['one'];
            } elseif ($data['count'] < 3 && ! empty($value['two'])) {
                $value = $value['two'];
            } else {
                $value = $value['other'] ?? $key;
            }
        }

        // Bail on keys which are not fully resolved
        if (is_array($value)) {
            return $key;
        }

        if (Str::contains($value, ['{{', '{%'])) {
            $value = liquid($value, $data, "trans:$key");
        }

        // Including HTML in output
        if (Str::endsWith($key, '_html')) {
            return $value;
        }

        return e($value);
    }

    /**
     * Retrieve translation keys.
     *
     * @return array
     */
    public function getTranslationKeys(): array
    {
        return reqcache("theme:{$this->theme->id}:translation_keys", function () {
            try {
                $asset = $this->asset('locales/' . $this->config->get('app.locale') . '.json');
            } catch (ModelNotFoundException $e) {
                /*
                    NOTE: In the liquid documentation, you'll typically see
                    that they will use a file naming structure for the default
                    locale, like, en-US.default.json and then the line below
                    would look for and load the default file if it didn't
                    find the given locale. https://shopify.dev/tutorials/develop-theme-localization-manage-locale-files#the-default-locale-file

                    Given some issues we've had with our translation
                    workflow process, we've decided to break with how they
                    do it and keep the file naming consistent across all locale
                    files. eg, {locale}.json

                    We're making this choice being aware that it means that (for now)
                    we're defaulting all themes defaulting to US english. If we need that
                    to be different in the future, we can cross that bridge when we come to it.
                */
                $asset = $this->asset(
                    $this->getAssetList('locales/en*.json')->first()
                );
            }

            return json_decode($asset->value, true);
        });
    }

    /**
     * Get the metadata for a given template.
     *
     * @param string $name
     * @return array
     */
    public function getTemplateMetadata($name)
    {
        return $this->metadata->getTemplateMetadata($name);
    }

    /**
     * Get the content editor classes.
     *
     * @param \Ds\Domain\Theming\MetadataTemplate[] $templates
     * @return string
     */
    public function getContentEditorClasses(array $templates)
    {
        return $this->metadata->getContentEditorClasses($templates);
    }

    /**
     * Get the last modified time of the theme styles.
     *
     * @return \Carbon\Carbon
     */
    public function getStylesModifiedTime()
    {
        if ($this->theme->locked) {
            $filePath = base_path("resources/themes/{$this->theme->handle}/styles/");

            $file = collect(File::allFiles($filePath))
                ->filter(function ($key) {
                    return Str::endsWith($key, '.scss');
                })->sortByDesc(function ($file) {
                    return $file->getMTime();
                })->first();

            return Carbon::createFromTimeStamp($file->getMTime());
        }

        $asset = $this->theme->assets()
            ->where('key', 'like', 'styles/%.scss')
            ->orderBy('updated_at', 'desc')
            ->first();

        return $asset->updated_at;
    }
}
