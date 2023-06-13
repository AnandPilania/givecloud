<?php

namespace Ds\Domain\Shared\Exceptions;

/**
 * Any exception implementing DisclosableException is expected to
 * contain a message that is suitable for disclosure to a user/visitor.
 *
 * No exception implementing DisclosableException will be reportable.
 */
interface DisclosableException
{
    public function getCode();

    public function getMessage();
}
