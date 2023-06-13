<?php

namespace Ds\Providers;

interface DomainEventServiceProviderInterface
{
    public static function listens(): array;
}
