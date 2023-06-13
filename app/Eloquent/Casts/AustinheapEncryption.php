<?php

namespace Ds\Eloquent\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;

class AustinheapEncryption implements CastsAttributes
{
    /** @var string */
    protected $header = "\x01\x02**ENCRYPTED**\x03\x1Eversion\x19VERSION-00-01-02\x17\x1Etype\x19string[native]\x17\x04";

    /** @var bool */
    protected $serializeAsJson;

    /**
     * Create a new instance.
     *
     * @param string|null $serializeAs
     * @return void
     */
    public function __construct($serializeAs = null)
    {
        $this->serializeAsJson = ($serializeAs === 'json');
    }

    /**
     * Cast the given value.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $key
     * @param mixed $value
     * @param array $attributes
     * @return mixed
     */
    public function get($model, $key, $value, $attributes)
    {
        // check for the austinheap header stop control character
        // and strip out the header if detected
        $offset = strpos($value, "\x04");

        if ($offset !== false) {
            $value = substr($value, $offset);
        }

        try {
            $value = Crypt::decrypt($value);
        } catch (DecryptException $e) {
            // do nothing
        }

        return $this->serializeAsJson ? json_decode($value, true) : $value;
    }

    /**
     * Prepare the given value for storage.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $key
     * @param array $value
     * @param array $attributes
     * @return string
     */
    public function set($model, $key, $value, $attributes)
    {
        if ($this->serializeAsJson) {
            $value = json_encode($value);
        }

        return $this->header . Crypt::encrypt($value);
    }
}
