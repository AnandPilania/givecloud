<?php

namespace Ds\Services;

use Illuminate\Support\Arr;

class FlashService
{
    /** @var bool */
    private $autoescape = true;

    /** @var array */
    private $cssClassses;

    /**
     * Create an instance.
     *
     * @param array $cssClassses
     */
    public function __construct(array $cssClassses = [])
    {
        $this->cssClassses = $cssClassses;
    }

    /**
     * Sets a success message.
     *
     * @param mixed $message
     */
    public function success($message)
    {
        session()->flash('_flashMessages.success', $message);
    }

    /**
     * Sets an error message.
     *
     * @param mixed $message
     */
    public function error($message)
    {
        session()->flash('_flashMessages.error', $message);
    }

    /**
     * Set the autoescape mode in generated html.
     *
     * @param bool $enabled
     */
    public function setAutoescape($enabled)
    {
        $this->autoescape = (bool) $enabled;
    }

    /**
     * Print the messages in the session flasher.
     */
    public function output()
    {
        $flashMessages = session('_flashMessages');

        foreach (Arr::wrap($flashMessages) as $type => $flashMessage) {
            $cssClasses = $this->cssClassses[$type] ?? '';
            $flashMessage = $this->autoescape ? e($flashMessage) : $flashMessage;

            if (empty($cssClasses)) {
                printf('<div>%s</div>' . PHP_EOL, $flashMessage);
            } else {
                printf('<div class="%s">%s</div>' . PHP_EOL, $cssClasses, $flashMessage);
            }
        }
    }
}
