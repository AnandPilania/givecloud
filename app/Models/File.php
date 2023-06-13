<?php

namespace Ds\Models;

use Ds\Eloquent\UploadableMedia;
use Ds\Eloquent\Userstamps;
use Ds\Illuminate\Database\Eloquent\Factories\HasFactory;
use Ds\Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class File extends Model implements UploadableMedia
{
    use HasFactory;
    use Userstamps;

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'id' => 'integer',
        'size' => 'integer',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        'download_url',
    ];

    public function variants(): HasMany
    {
        return $this->hasMany(VariantFile::class, 'fileid');
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    /**
     * Attribute Mutator: Download URL
     *
     * @return string
     */
    public function getDownloadUrlAttribute()
    {
        return secure_site_url("/jpanel/downloads/{$this->id}");
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
     * Attribute Mutator: Internal CDN URI
     *
     * @return string
     */
    public function getInternalCdnUriAttribute()
    {
        $uri = Storage::disk('cdn')->url("downloads/{$this->filename}");
        /* Replace protocal with google cloud prototal to save on transfer cost */
        if (Str::startsWith($uri, 'https://cdn.givecloud.co')) {
            $uri = substr_replace($uri, 'gs', 0, strlen('https'));
        }

        return $uri;
    }

    /**
     * Attribute Mutator: Temporary URL
     *
     * @return string
     */
    public function getTemporaryUrlAttribute()
    {
        // return Storage::disk('cdn')->temporaryUrl("downloads/{$this->filename}", now()->addMinutes(5));

        $url = app('cdn')->getObject("downloads/{$this->filename}")
            ->signedUrl(now()->addMinutes(5));

        return Storage::disk('cdn')->url("downloads/{$this->filename}") . '?' . parse_url($url, PHP_URL_QUERY);
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
     * Attribute Mutator: Font Awesome Icon
     *
     * @return string
     */
    public function getFaIconAttribute()
    {
        if (Str::startsWith($this->content_type, 'audio/')) {
            return 'fa-file-audio-o';
        }

        if (Str::startsWith($this->content_type, 'image/')) {
            return 'fa-file-image-o';
        }

        if (Str::startsWith($this->content_type, 'video/')) {
            return 'fa-file-video-o';
        }

        $books = [
            'application/pdf',
            'application/msword',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];

        if (in_array($this->content_type, $books)) {
            return 'fa-book';
        }

        return 'fa-file';
    }
}
