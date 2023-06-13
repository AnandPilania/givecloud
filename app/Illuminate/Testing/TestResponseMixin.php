<?php

namespace Ds\Illuminate\Testing;

use Illuminate\Testing\Assert as PHPUnit;

/** @mixin \Illuminate\Testing\TestResponse */
class TestResponseMixin
{
    /**
     * Assert that the expected value and type exists at the given path in the response.
     */
    public function assertJsonPathEquals()
    {
        return function ($path, $expect) {
            PHPUnit::assertEquals($expect, $this->decodeResponseJson()->json($path));

            return $this;
        };
    }

    /**
     * Assert that the session has the given flash messages.
     */
    public function assertSessionHasFlashMessages()
    {
        return function ($keys = []) {
            $this->assertSessionHas('_flashMessages');

            $keys = (array) $keys;

            $messages = $this->session()->get('_flashMessages');

            foreach ($keys as $key => $value) {
                if (is_int($key)) {
                    PHPUnit::assertTrue(isset($messages[$value]), "Session missing flash message: $value");
                } else {
                    PHPUnit::assertEquals($value, $messages[$key] ?? null);
                }
            }

            return $this;
        };
    }
}
