<?php

namespace Ds\Domain\Messenger;

use Exception;

class ResumableConversationException extends Exception
{
    /** @var string */
    protected $resumeOn;

    /** @var string[] */
    protected $textMessages = [];

    public function __construct(?string $resumeOn = 'payment_method_added')
    {
        $this->resumeOn = $resumeOn;

        parent::__construct('', 200);
    }

    /**
     * Get trigger for resuming conversation.
     */
    public function getResumeOn(): string
    {
        return $this->resumeOn;
    }

    /**
     * @return string[]
     */
    public function getTextMessages(): array
    {
        return $this->textMessages;
    }

    public function setTextMessages(array $textMessages = []): self
    {
        $this->textMessages = $textMessages;

        return $this;
    }
}
