<?php

namespace Tests;

use Mockery\ExpectationInterface;
use Mockery\MockInterface;

/** @mixin \Mockery\Mock */
class MockeryMockProxy
{
    /** @var \Mockery\MockInterface */
    public $mock;

    public function __construct(MockInterface $mock)
    {
        $this->mock = $mock;
    }

    /**
     * @param string $method
     * @param array $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        $result = $this->mock->{$method}(...$parameters);

        if ($result instanceof ExpectationInterface) {
            return new MockeryExpectationProxy($result);
        }

        return $result;
    }
}
