<?php

namespace Ds\Domain\Messenger;

/**
 * Provide access to BotMan properties and methods transparently
 * inorder to prevent issues with conversations and closure serialization.
 */
class SerializableBotMan
{
    /**
     * @param string $name
     */
    public function __get($name)
    {
        return app(BotMan::class)->{$name};
    }

    /**
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public function __call($method, $args)
    {
        return app(BotMan::class)->{$method}(...$args);
    }
}
