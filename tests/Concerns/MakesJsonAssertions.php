<?php

namespace Tests\Concerns;

use Illuminate\Support\Arr;
use Illuminate\Testing\AssertableJsonString;
use Illuminate\Testing\Fluent\AssertableJson;

trait MakesJsonAssertions
{
    /**
     * @param mixed $actual
     * @param array|callable $expected
     * @param bool $strict
     */
    public function assertJsonable($actual, $expected, bool $strict = false): void
    {
        $json = new AssertableJsonString(
            is_string($actual) ? $actual : json_encode($actual),
        );

        if (is_array($expected)) {
            $json->assertSubset($expected, $strict);
        } else {
            $assert = AssertableJson::fromAssertableJsonString($json);

            $expected($assert);

            if (Arr::isAssoc($assert->toArray())) {
                $assert->interacted();
            }
        }
    }
}
