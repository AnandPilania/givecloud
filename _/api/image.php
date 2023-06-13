<?php

function media_thumbnail($media, $options = null)
{
    static $cache = [];

    if (empty($media)) {
        return '';
    }

    if (is_string($media)) {
        $hash = sha1($media) . ':' . sha1(json_encode($options));
    } elseif (is_numeric($media)) {
        $hash = 'Ds\\Models\\Media:' . $media . ':' . sha1(json_encode($options));
    } elseif (is_instanceof($media, \Ds\Domain\Theming\Liquid\Drops\MediaDrop::class)) {
        $hash = get_class($media->source) . ':' . $media->source->id . ':' . sha1(json_encode($options));
    } elseif (is_instanceof($media, \Illuminate\Database\Eloquent\Model::class)) {
        $hash = get_class($media) . ':' . $media->id . ':' . sha1(json_encode($options));
    } elseif (isset($media->media_id)) {
        $media = $media->media_id;
        $hash = 'Ds\\Models\\Media:' . $media . ':' . sha1(json_encode($options));
    } else {
        $error = gettype($media);

        if ($error === 'object') {
            $error = get_class($media);
        }

        throw new \InvalidArgumentException($error);
    }

    if (array_key_exists($hash, $cache)) {
        return $cache[$hash];
    }

    if (is_string($options) || is_numeric($options)) {
        $options = ['size' => $options];
    }

    return $cache[$hash] = image_thumbnail($media, $options);
}

function image_thumbnail($imageUrl, $regenerate = false)
{
    $mediaThumbnail = function ($model) use ($regenerate) {
        if ($model instanceof \Ds\Domain\Theming\Liquid\Drops\MediaDrop) {
            $model = $model->source;
        } elseif ($model instanceof \Ds\Models\Post && $model->featuredImage) {
            $model = $model->featureImage;
        } elseif ($model instanceof \Ds\Models\PostType && $model->photo) {
            $model = $model->photo;
        } elseif ($model instanceof \Ds\Models\Product && $model->photo) {
            $model = $model->photo;
        } elseif ($model instanceof \Ds\Models\ProductCategory && $model->photo) {
            $model = $model->photo;
        } elseif ($model instanceof \Ds\Domain\Sponsorship\Models\Sponsorship && $model->featuredImage) {
            $model = $model->featuredImage;
        }

        if ($model instanceof \Ds\Models\Media) {
            if (is_array($regenerate) && count($regenerate)) {
                return $model->getImageUrl($regenerate);
            }

            return $model->thumbnail_url;
        }

        return '';
    };

    // Accept Media IDs
    if (is_numeric($imageUrl)) {
        return $mediaThumbnail(\Ds\Models\Media::find($imageUrl));
    }

    // Accept models
    if (is_object($imageUrl)) {
        return $mediaThumbnail($imageUrl);
    }

    // Accept URLs
    if (is_string($imageUrl)) {
        // Check for Givecloud CDN URLs (** these shouldn't ever actually be used)
        if (preg_match("#^(?:https?://cdn.givecloud.co/s/files/\d(?:-dev|)/\d{4}/\d{4})/([^/]+)/(.*)$#i", $imageUrl, $matches)) {
            try {
                $opts = \Ds\Models\Media::getImageOptions(basename(parse_url($imageUrl, PHP_URL_PATH)));
                $media = \Ds\Models\Media::where('collection_name', $matches[1])->where('filename', $opts['name'])->firstOrFail();

                return $mediaThumbnail($media);
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                return $imageUrl;
            }
        }

        return $imageUrl;
    }

    // Return empty GIF for unsupported types
    return 'data:image/gif;base64,R0lGODlhAQABAIAAAMLCwgAAACH5BAAAAAAALAAAAAABAAEAAAICRAEAOw==';
}
