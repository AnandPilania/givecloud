<?php

namespace Ds\Eloquent;

/**
 * @property \Ds\Eloquent\MetadataCollection $metadata
 */
interface Metadatable
{
    /**
     * Helper for getting and setting metadata value(s).
     *
     * @param string|array $key
     * @param mixed $defaultValue
     * @return mixed
     */
    public function metadata($key = null, $defaultValue = null);

    /**
     * Get metadata.
     *
     * @param string|null $key
     * @return \Ds\Models\Metadata|null
     */
    public function getMetadata($key = null);

    /**
     * Set metadata value(s).
     *
     * @param string|array $key
     * @param mixed $value
     */
    public function setMetadata($key, $value = null);

    /**
     * Attribute Mask: Metadata.
     *
     * @return \Ds\Eloquent\MetadataCollection
     */
    public function getMetadataAttribute();

    /**
     * Attribute Mutator: Metadata.
     *
     * @param array $value
     */
    public function setMetadataAttribute(array $value);
}
