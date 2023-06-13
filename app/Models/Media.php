<?php

namespace Ds\Models;

use Ds\Common\TemporaryFile;
use Ds\Domain\Theming\Liquid\Liquidable;
use Ds\Eloquent\Hashids;
use Ds\Eloquent\UploadableMedia;
use Ds\Eloquent\Userstamps;
use Ds\Illuminate\Database\Eloquent\Factories\HasFactory;
use Ds\Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Http\File;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use League\MimeTypeDetection\ExtensionMimeTypeDetector;
use SplFileInfo;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Media extends Model implements Liquidable, UploadableMedia
{
    use Hashids;
    use HasFactory;
    use Userstamps;

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'parent_id' => 'integer',
        'size' => 'integer',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'is_audio',
        'is_image',
        'is_video',
        'public_url',
        'thumbnail_url',
    ];

    public function conversions(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    /**
     * Set the visibility.
     *
     * @param string $visibility
     */
    public function setVisibility($visibility)
    {
        Storage::disk('cdn')->setVisibility("{$this->collection_name}/{$this->filename}", $visibility);
    }

    /**
     * Get copy of media as a temporary file.
     *
     * @return \Ds\Common\TemporaryFile|null
     */
    public function getAsTemporaryFile(): ?TemporaryFile
    {
        $contents = Storage::disk('cdn')->get("{$this->collection_name}/{$this->filename}");

        return new TemporaryFile(
            $contents,
            pathinfo($this->filename, PATHINFO_EXTENSION)
        );
    }

    public function getCachedTemporaryFile(): ?TemporaryFile
    {
        $key = "{$this->collection_name}/{$this->filename}";

        return Cache::remember($key, now()->addDay(), function () {
            return $this->getAsTemporaryFile()->setFilesystemCleanup(false);
        });
    }

    /**
     * Upload file to CDN and return the Media instance.
     *
     * @param string $path
     * @param array $attributes
     * @return \Ds\Models\Media
     */
    public static function store(string $path, array $attributes = [])
    {
        $file = new File($path);

        $filename = Arr::get($attributes, 'filename', $file->getFilename());
        $filename = static::getUniqueFilename($filename);

        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        $media = new static;
        $media->parent_id = Arr::get($attributes, 'parent_id');
        $media->collection_name = Arr::get($attributes, 'collection_name', 'files');
        $media->name = pathinfo($filename, PATHINFO_FILENAME);
        $media->filename = $filename;
        $media->content_type = $file->getMimeType() ?? (new ExtensionMimeTypeDetector)->detectMimeTypeFromPath($extension);
        $media->size = $file->getSize();
        $media->caption = Arr::get($attributes, 'caption');

        Storage::disk('cdn')->putFileAs(
            $media->collection_name,
            $file,
            $media->filename,
            Arr::get($attributes, 'visibility', 'public')
        );

        $media->save();

        event(new \Ds\Events\MediaUploaded($media));

        return $media;
    }

    /**
     * Upload file to CDN and return the Media instance.
     *
     * @param \Symfony\Component\HttpFoundation\File\File $file
     * @param array $attributes
     * @return \Ds\Models\Media
     */
    public static function storeFile(SplFileInfo $file, array $attributes = [])
    {
        if (array_key_exists('filename', $attributes) === false && $file instanceof UploadedFile) {
            $attributes['filename'] = $file->getClientOriginalName();
        }

        return static::store($file->getPathname(), $attributes);
    }

    /**
     * Upload file to CDN and return the Media instance.
     *
     * @param string $name
     * @param array $attributes
     * @return \Ds\Models\Media|null
     */
    public static function storeUpload(string $name, array $attributes = [])
    {
        $file = request()->file($name);

        if ($file && $file->isValid() && $file->getMimeType() !== 'inode/x-empty') {
            return static::storeFile($file, $attributes);
        }
    }

    /**
     * Upload url to CDN and return the Media instance.
     *
     * @param string $url
     * @param array $attributes
     * @return \Ds\Models\Media
     */
    public static function storeUrl(string $url, array $attributes = [])
    {
        $tmpFile = new TemporaryFile(
            (string) Http::get($url)->throw()
        );

        $file = new UploadedFile(
            $tmpFile->getFilename(),
            basename(parse_url($url, PHP_URL_PATH))
        );

        return self::storeFile($file, $attributes);
    }

    /**
     * Get a unique name for a given file.
     *
     * @param string $filename
     * @return string
     */
    public static function getUniqueFilename($filename)
    {
        $filename = sanitize_filename($filename);

        $name = pathinfo($filename, PATHINFO_FILENAME);
        $extension = pathinfo($filename, PATHINFO_EXTENSION);

        $existing = static::query()
            ->where('name', $name)
            ->orWhere(function ($query) use ($name) {
                $query->where('name', 'rlike', '^' . preg_quote($name) . '-[0-9]+$');
            })->orderByRaw('LENGTH(name) DESC')
            ->orderBy('name', 'desc')
            ->value('name');

        if ($existing) {
            $sequence = preg_replace('/^' . preg_quote($name) . '-([0-9]+)$/', '$1', $existing);
            $filename = sprintf("$name-%d.$extension", is_numeric($sequence) ? $sequence + 1 : 1);
        }

        return $filename;
    }

    /**
     * Get list of all the content types associated with documents.
     *
     * @return array
     */
    public function getDocumentContentTypesAttribute()
    {
        return [
            'application/pdf',
            'application/msword',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];
    }

    /**
     * Scope: Audio
     *
     * @param \Illuminate\Database\Query\Builder $query
     */
    public function scopeAudio($query)
    {
        $query->where('content_type', 'like', 'audio/%');
    }

    /**
     * Scope: Collection
     *
     * @param \Illuminate\Database\Query\Builder $query
     * @param string $value
     */
    public function scopeCollection($query, $value)
    {
        $query->where('collection_name', $value);
    }

    /**
     * Scope: Documents
     *
     * @param \Illuminate\Database\Query\Builder $query
     */
    public function scopeDocuments($query)
    {
        $query->whereIn('content_type', $this->document_content_types);
    }

    /**
     * Scope: Images
     *
     * @param \Illuminate\Database\Query\Builder $query
     */
    public function scopeImages($query)
    {
        $query->where('content_type', 'like', 'image/%');
    }

    /**
     * Scope: Videos
     *
     * @param \Illuminate\Database\Query\Builder $query
     */
    public function scopeVideos($query)
    {
        $query->where('content_type', 'like', 'video/%');
    }

    /**
     * Attribute Mutator: Is Audio
     *
     * @return bool
     */
    public function getIsAudioAttribute()
    {
        return Str::startsWith($this->content_type, 'audio/');
    }

    /**
     * Attribute Mutator: Is Document
     *
     * @return string|null
     */
    public function getIsDocumentAttribute()
    {
        if (in_array($this->content_type, $this->document_content_types)) {
            return 'fa-book';
        }
    }

    /**
     * Attribute Mutator: Is Image
     *
     * @return bool
     */
    public function getIsImageAttribute()
    {
        return Str::startsWith($this->content_type, 'image/');
    }

    /**
     * Attribute Mutator: Is Video
     *
     * @return bool
     */
    public function getIsVideoAttribute()
    {
        return Str::startsWith($this->content_type, 'video/');
    }

    /**
     * Attribute Mutator: Temporary URL
     *
     * @return string
     */
    public function getTemporaryUrlAttribute()
    {
        // return Storage::disk('cdn')->temporaryUrl("downloads/{$this->filename}", now()->addMinutes(5));

        $url = app('cdn')->getObject("{$this->collection_name}/{$this->filename}")
            ->signedUrl(now()->addMinutes(5));

        return Storage::disk('cdn')->url("{$this->collection_name}/{$this->filename}") . '?' . parse_url($url, PHP_URL_QUERY);
    }

    /**
     * Attribute Mutator: Public URL
     *
     * @return string
     */
    public function getPublicUrlAttribute()
    {
        $prefix = site('cdn_path_prefix');

        // some tomfoolery to allow staging/copied sites to display
        // media that was uploaded to the original site to still be displayed
        // on the staging/copied site without having to actually duplicate
        // the media from the original site under the staging site
        if (sys_get('original_cdn_path_prefix') && $this->_transfered) {
            $prefix = sys_get('original_cdn_path_prefix');
        }

        return "https://cdn.givecloud.co/$prefix/{$this->collection_name}/{$this->filename}";
    }

    /**
     * Attribute Mutator: Internal CDN URI
     *
     * @return string
     */
    public function getInternalCdnUriAttribute()
    {
        $uri = $this->getPublicUrlAttribute();
        /* Replace protocal with google cloud prototal to save on transfer cost */
        if (Str::startsWith($uri, 'https://cdn.givecloud.co')) {
            $uri = substr_replace($uri, 'gs', 0, strlen('https'));
        }

        return $uri;
    }

    /**
     * Attribute Mutator: Thumbnail URL
     *
     * @return string
     */
    public function getThumbnailUrlAttribute()
    {
        $size = str_replace('?', '', sys_get('thumbnail_size'));

        return $this->getImageUrl([
            'size' => $size,
            'crop' => sys_get('thumbnail_crop', 'entropy'),
        ]);
    }

    /**
     * Get a URL for a given set of image options.
     *
     * @param array $options
     * @return string
     */
    public function getImageUrl(array $options = [])
    {
        $sizes = [
            16 => 'pico',
            32 => 'icon',
            50 => 'thumb',
            100 => 'small',
            160 => 'compact',
            240 => 'medium',
            480 => 'large',
            600 => 'grande',
        ];

        $crops = ['top', 'center', 'bottom', 'left', 'right', 'entropy', 'attention', 'face'];

        $options = collect([
            'size' => Arr::get($options, 0, 'small'),
            'crop' => null,
            'scale' => null,
            'radius' => null,
            'trim' => null,
            'format' => null,
        ])->merge($options)->map(function ($option) {
            return is_string($option) ? mb_strtolower($option) : $option;
        });

        if (! $this->is_image) {
            if ($this->content_type === 'application/pdf') {
                $options['format'] = 'png';
            } else {
                // return placeholder for all non-image/PDF media
                return 'data:image/gif;base64,R0lGODlhAQABAIAAAMLCwgAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw==';
            }
        }

        $suffix = '_';

        $path = array_map('mb_strtolower', pathinfo($this->public_url)) + [
            'dirname' => '',
            'filename' => '',
            'extension' => 'jpg',
        ];

        //
        // The load balancer forwards `s/files/1/` prefixed URIs directly to Google Cloud Storage
        // and `s/files/2/` prefixed URIs to our Google Cloud Functions imagify function.
        //
        // So we need to switch the URL context so the load balancer will direct the request
        // to the imagify backend which generates the images
        //

        $path['dirname'] = preg_replace('#s/files/1(-dev|)/#', 's/files/2$1/', $path['dirname']);

        // parse sizing options
        if (preg_match('/^([0-9]*)(x?)([0-9]*)$/', $options['size'], $matches)) {
            if ($matches[1] && $matches[2] && $matches[3]) {
                $suffix .= $matches[1] . 'x' . $matches[3];
            } elseif ($matches[1] === $matches[3] || ($matches[1] && ! $matches[2])) {
                if (array_key_exists($matches[1], $sizes)) {
                    $suffix .= $sizes[$matches[1]];
                } else {
                    $suffix .= $matches[1] . 'x' . $matches[1];
                }
            } elseif ($matches[1]) {
                $suffix .= $matches[1] . 'x';
            } elseif ($matches[3]) {
                $suffix .= 'x' . $matches[3];
            } else {
                return '';
            }
        } elseif (in_array($options['size'], $sizes)) {
            $suffix .= $options['size'];
        } else {
            return '';
        }

        // parse cropping options
        if ($options['crop']) {
            if (in_array($options['crop'], $crops)) {
                $suffix .= "_cropped_{$options['crop']}";
            } else {
                return '';
            }
        }

        // parse scaling options
        if ($options['scale']) {
            if (in_array($options['scale'], [2, 3])) {
                $suffix .= "@{$options['scale']}x";
            } else {
                return '';
            }
        }

        if ($options['radius']) {
            $suffix .= "_r{$options['radius']}";
        }

        if ($options['trim'] === true || is_numeric($options['trim'])) {
            $suffix .= '_t' . ($options['trim'] === true ? 0 : (int) abs($options['trim']));
        }

        // use PNG as format for SVGs when no explicit format is provided
        if ($path['extension'] === 'svg' && empty($options['format'])) {
            $options['format'] = 'png';
        }

        // parse format options
        if ($options['format']) {
            if ($options['format'] === 'pjpg') {
                $options['format'] = 'jpg';
                $suffix .= '.progressive.';
            }
            if ($options['format'] !== $path['extension']) {
                if (! ($options['format'] === 'jpg' && $path['extension'] === 'jpeg')) {
                    $path['extension'] .= '.' . $options['format'];
                }
            }
        }

        return $path['dirname'] . '/' . $path['filename'] . $suffix . '.' . $path['extension'];
    }

    /**
     * Get set of image options for a given URL.
     *
     * @param string $filename
     * @return array
     */
    public static function getImageOptions(string $filename): array
    {
        $re = implode('', [
            '(?:',
            '_([0-9]+x[0-9]+|[0-9]+x|x[0-9]+|pico|icon|thumb|small|compact|medium|large|grande)',
            '(?:_(cropped_top|cropped_center|cropped_bottom|cropped_left|cropped_right|cropped_attention|cropped_entropy|cropped_face|cropped))?',
            '(?:@([23])x)?',
            '(?:_r([0-9]+))?',
            '(?:_t([0-9]+|rimmed))?',
            '(?:[.](progressive))?',
            ')',
            '([.][^.]+)([.](?:jpg|png))?$',
        ]);

        $opts = [
            'name' => $filename,
            'size' => null,
            'crop' => null,
            'scale' => null,
            'radius' => null,
            'trim' => null,
            'format' => null,
        ];

        if (! preg_match("/$re/i", $filename, $matches)) {
            return $opts;
        }

        switch (Arr::get($matches, 1)) {
            case 'pico':    $opts['size'] = ['width' => 16, 'height' => 16]; break;
            case 'icon':    $opts['size'] = ['width' => 32, 'height' => 32]; break;
            case 'thumb':   $opts['size'] = ['width' => 50, 'height' => 50]; break;
            case 'small':   $opts['size'] = ['width' => 100, 'height' => 100]; break;
            case 'compact': $opts['size'] = ['width' => 160, 'height' => 160]; break;
            case 'medium':  $opts['size'] = ['width' => 240, 'height' => 240]; break;
            case 'large':   $opts['size'] = ['width' => 480, 'height' => 480]; break;
            case 'grande':  $opts['size'] = ['width' => 600, 'height' => 600]; break;
            default:
                $size = explode('x', Arr::get($matches, 1));
                $opts['size'] = [
                    'width' => Arr::get($size, 0, null),
                    'height' => Arr::get($size, 1, null),
                ];
        }

        if (Arr::get($matches, 2)) {
            $crop = explode('_', $matches[2]);
            $opts['crop'] = Arr::get($crop, 1, 'entropy');
        }

        $scale = Arr::get($matches, 3);
        if ($scale) {
            $opts['scale'] = (int) $scale;
        }

        $radius = Arr::get($matches, 4);
        if ($radius) {
            $opts['radius'] = (int) $radius;
        }

        $trim = Arr::get($matches, 5);
        if ($trim) {
            $opts['trim'] = $trim === 'rimmed' ? 0 : (int) $trim;
        }

        if (Arr::get($matches, 6)) {
            $opts['format'] = 'pjpg';
        }

        $ext = '.' . mb_strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (Arr::get($matches, 8)) {
            $ext = Arr::get($matches, 7);
            if ($matches[8] === '.jpg' && $opts['format'] !== 'pjpg') {
                $opts['format'] = 'jpg';
            }
        } else {
            if ($opts['format'] === 'pjpg') {
                throw new InvalidArgumentException('Progressive JPGs must have a .jpg or .jpeg file extension.');
            }
        }

        $opts['name'] = mb_substr($filename, 0, mb_strlen($filename) - mb_strlen($matches[0])) . $ext;

        return $opts;
    }

    public static function findByUrl(?string $url): ?self
    {
        $baseUrl = sprintf('https://cdn.givecloud.co/%s/', site('cdn_path_prefix'));

        if (empty($url) || ! Str::startsWith($url, $baseUrl)) {
            return null;
        }

        $filename = Str::of($url)->after($baseUrl)->explode('/')[1] ?? null;

        if (empty($filename)) {
            return null;
        }

        $options = self::getImageOptions($filename);

        return self::where('filename', $options['name'])->first();
    }

    /**
     * Liquid representation of model.
     */
    public function toLiquid()
    {
        return \Ds\Domain\Theming\Liquid\Drop::factory($this, 'Media');
    }
}
