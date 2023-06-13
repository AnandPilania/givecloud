<?php

namespace Tests\Concerns;

trait MakesArrayAssertions
{
    /**
     * Assert that a $haystack array contains an array with $key equals to the $needle.
     */
    public function assertArrayHasArrayWithValue(array $haystack, $needle, string $key = 'name')
    {
        $this->assertIsArray($haystack);
        $this->assertTrue(array_search($needle, array_map(fn ($hay) => $hay[$key], $haystack), true) !== false);
    }
}
