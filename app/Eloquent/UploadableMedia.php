<?php

namespace Ds\Eloquent;

/**
 * @property-read bool $is_image
 * @property-read string $internal_cdn_uri
 */
interface UploadableMedia
{
    /**
     * Relationship: Tags
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany
     */
    public function tags();

    /**
     * Attribute Mutator: Is Image
     *
     * @return bool
     */
    public function getIsImageAttribute();

    /**
     * Attribute Mutator: Internal CDN URI
     *
     * @return string
     */
    public function getInternalCdnUriAttribute();
}
