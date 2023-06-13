<?php

namespace Ds\Domain\Theming;

use Thunder\Shortcode\Shortcode\ShortcodeInterface;

abstract class Shortcode
{
    /** @var string */
    protected $invokeKey;

    /** @var array */
    protected static $invoked = [];

    /** @var \Thunder\Shortcode\Shortcode\ShortcodeInterface */
    protected $shortcode;

    /**
     * Output the collections template.
     *
     * @param \Thunder\Shortcode\Shortcode\ShortcodeInterface $shortcode
     * @return string
     */
    public function __invoke(ShortcodeInterface $shortcode)
    {
        $this->shortcode = $shortcode;

        if ($this->invokeKey) {
            $key = $shortcode->getName() . ':' . $shortcode->getParameter($this->invokeKey);

            if (isset(static::$invoked[$key])) {
                return '';
            }

            static::$invoked[$key] = true;
        }

        $output = $this->handle($shortcode);

        if (isset($key)) {
            unset(static::$invoked[$key]);
        }

        return $output;
    }

    /**
     * Output the collections template.
     *
     * @param \Thunder\Shortcode\Shortcode\ShortcodeInterface $s
     * @return string
     */
    abstract public function handle(ShortcodeInterface $s);

    /**
     * Format a shortcode error message.
     *
     * @param string $message
     * @return string
     */
    protected function error($message)
    {
        $output = sprintf("<!-- shortcode error message from Givecloud (%s): %s -->\n", $this->shortcode->getName(), $message);

        if (user()) {
            $output .= "<div style=\"background-color:#f30 !important; font-size:16px !important; line-height:20px !important; z-index:99999999999; border-radius:3px; display:inline-block !important; padding:2px 6px !important; color:#fff !important; font-weight:bold !important; font-family:monospace !important; text-align:center !important;\">$message</div>";
        }

        return $output;
    }
}
