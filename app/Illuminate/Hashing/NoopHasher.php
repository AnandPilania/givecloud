<?php

namespace Ds\Illuminate\Hashing;

use Illuminate\Contracts\Hashing\Hasher as HasherContract;

class NoopHasher implements HasherContract
{
    /**
     * Get information about the given hashed value.
     *
     * @param string $hashedValue
     * @return array
     */
    public function info($hashedValue)
    {
        return [
            'algo' => 0,
            'algoName' => 'unknown',
            'options' => [],
        ];
    }

    /**
     * Hash the given value.
     *
     * @param string $value
     * @param array $options
     * @return string
     */
    public function make($value, array $options = [])
    {
        return $value;
    }

    /**
     * Check the given plain value against a hash.
     *
     * @param string $value
     * @param string $hashedValue
     * @param array $options
     * @return bool
     */
    public function check($value, $hashedValue, array $options = [])
    {
        return $value === $hashedValue;
    }

    /**
     * Check if the given hash has been hashed using the given options.
     *
     * @param string $hashedValue
     * @param array $options
     * @return bool
     */
    public function needsRehash($hashedValue, array $options = [])
    {
        return false;
    }
}
