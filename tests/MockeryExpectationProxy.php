<?php

namespace Tests;

use Illuminate\Support\Traits\Macroable;
use Mockery\ExpectationInterface;

/**
 * @mixin \Mockery\Expectation
 *
 * @method self andReturnStripe(string $name)
 */
class MockeryExpectationProxy
{
    use Macroable {
        Macroable::__call as macroCall;
    }

    /** @var \Mockery\ExpectationInterface */
    public $expectation;

    public function __construct(ExpectationInterface $expectation)
    {
        $this->expectation = $expectation;
    }

    /**
     * @param string $method
     * @param array $parameters
     * @return \Tests\MockeryExpectationProxy|mixed
     */
    public function __call($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        $result = $this->expectation->{$method}(...$parameters);

        if ($result instanceof ExpectationInterface) {
            return new static($result);
        }

        return $result;
    }
}
