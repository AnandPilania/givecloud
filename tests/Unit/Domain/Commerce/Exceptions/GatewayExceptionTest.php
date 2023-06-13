<?php

namespace Tests\Unit\Domain\Commerce\Exceptions;

use Ds\Domain\Commerce\Exceptions\GatewayException;
use Exception;
use Tests\TestCase;

class GatewayExceptionTest extends TestCase
{
    public function testMessageGetCastedToString(): void
    {
        $exception = new GatewayException(null);

        $this->assertInstanceOf(Exception::class, $exception);
        $this->assertSame('', $exception->getMessage());
    }
}
