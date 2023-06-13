<?php

namespace Ds\Models;

use Carbon\Carbon;
use Defr\PhpMimeType\MimeType;
use Ds\Domain\Theming\Liquid\Template;
use Ds\Domain\Theming\Theme as ThemeService;
use Ds\Eloquent\Userstamps;
use Ds\Illuminate\Database\Eloquent\Factories\HasFactory;
use Ds\Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class Asset extends Model
{
    use HasFactory;
    use Userstamps;

    /** @var \Ds\Domain\Theming\Theme */
    protected $themeService;

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['name'];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'theme_id' => 'integer',
        'parent_id' => 'integer',
        'locked' => 'boolean',
        'size' => 'integer',
    ];

    public function theme(): BelongsTo
    {
        return $this->belongsTo(Theme::class);
    }

    /**
     * Attribute Mutator: Name
     *
     * @return string
     */
    public function getNameAttribute()
    {
        return basename($this->key);
    }

    /**
     * Attribute Mutator: Key
     *
     * @param string $key
     */
    public function setKeyAttribute($key)
    {
        $this->attributes['key'] = $key;

        MimeType::$mimeTypes['scss'] = 'text/scss';
        MimeType::$mimeTypes['liquid'] = 'text/x-liquid';

        $this->content_type = MimeType::get($key);
    }

    /**
     * Attribute Mutator: Value
     *
     * @param string $value
     */
    public function setValueAttribute($value)
    {
        $this->attributes['value'] = $value;

        $this->size = strlen($value);
    }

    /**
     * Attribute Mutator: Value Compiled
     *
     * @return string
     */
    public function getValueCompiledAttribute()
    {
        if ($this->content_type === 'text/x-liquid' && Str::startsWith($this->key, 'content/')) {
            return (new Template(Str::after($this->key, 'content/'), 'content/'))->render();
        }

        if ($this->content_type === 'text/scss') {
            $key = "theme-{$this->theme_id}--{$this->key}";

            $cache = Cache::tags('theming')->get($key);

            if (! $cache || $cache['modified']->lessThan(
                $modified = $this->getThemeService()->getStylesModifiedTime()
            )) {
                if ($this->key === 'styles/theme.scss') {
                    $value = "{$this->value}\n\n" . volt_setting('css_overrides');
                } else {
                    $value = $this->value;
                }

                $handleException = function ($e) {
                    $error = $e->getMessage();
                    $error = preg_replace('#/data/envoyer/.*/resources/themes/[^/]+/#', '', $error);
                    $error = preg_replace('#/data/envoyer/.*/resources/theming/scss/#', '', $error);

                    return <<<CONTENT

/******************************************************************************************

    SCSS ERROR:
    ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    $error

*******************************************************************************************/

CONTENT;
                };

                try {
                    $compiled = app('scss')->compile($value);
                } catch (\ScssPhp\ScssPhp\Exception\CompilerException $e) {
                    $compiled = $handleException($e);
                } catch (\ScssPhp\ScssPhp\Exception\ParserException $e) {
                    $compiled = $handleException($e);
                } catch (\ScssPhp\ScssPhp\Exception\RangeException $e) {
                    $compiled = $handleException($e);
                }

                Cache::tags('theming')->forever($key, $cache = [
                    'modified' => Carbon::now(),
                    'value' => $compiled,
                ]);
            }

            return $cache['value'];
        }

        return $this->value;
    }

    /**
     * Attribute Mutator: Cache Buster
     *
     * @return string
     */
    public function getCacheBusterAttribute()
    {
        $modified = fromUtc($this->updated_at);

        if ($this->content_type === 'text/scss') {
            $updated = Cache::tags('theming')->get('settings_updated');

            if ($updated && fromUtc($updated)->greaterThan($modified)) {
                $modified = fromUtc($updated);
            }
        }

        return substr(sha1(fromUtcFormat($modified, 'U')), 0, 10);
    }

    /**
     * Attribute Mutator: Public Url
     *
     * @return string
     */
    public function getPublicUrlAttribute()
    {
        return secure_site_url("/static/{$this->theme->handle}/{$this->key}?v={$this->cache_buster}");
    }

    /**
     * Get the relevant theme service.
     *
     * @return \Ds\Domain\Theming\Theme
     */
    public function getThemeService()
    {
        return $this->themeService ?? app('theme');
    }

    /**
     * Get the relevant theme service.
     *
     * @param \Ds\Domain\Theming\Theme $themeService
     * @return static
     */
    public function setThemeService(ThemeService $themeService)
    {
        $this->themeService = $themeService;

        return $this;
    }

    /**
     * Get a Response for the asset.
     *
     * @return \Illuminate\Http\Response
     */
    public function toResponse()
    {
        $res = new \Illuminate\Http\Response($this->value);
        $res->header('Content-Type', $this->content_type);

        if ($this->content_type === 'text/scss') {
            $res->header('Content-Type', 'text/css');
            $res->setContent($this->value_compiled);
        }

        if ($this->content_type === 'text/x-liquid' && Str::startsWith($this->key, 'content/')) {
            $res->header('Content-Type', 'text/html');
            $res->setContent($this->value_compiled);
        }

        $maxAge = now()->addDays(7);

        if (Str::startsWith($this->content_type, 'image/')) {
            $maxAge = now()->addDays(30);
        }

        $res->setPublic();
        $res->setMaxAge($maxAge->diffInSeconds());
        $res->setExpires($maxAge);

        return $res;
    }
}
