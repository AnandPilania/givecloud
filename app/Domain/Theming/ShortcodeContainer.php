<?php

namespace Ds\Domain\Theming;

use Closure;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;
use Thunder\Shortcode\HandlerContainer\HandlerContainerInterface;
use Thunder\Shortcode\Parser\RegularParser;
use Thunder\Shortcode\Processor\Processor;

class ShortcodeContainer implements HandlerContainerInterface
{
    /** @var callable[] */
    private $handlers = [];

    /**
     * Add handle for shortcode name.
     *
     * @param string $name
     * @param callable $callable
     */
    public function add($name, callable $callable)
    {
        $this->handlers[$name] = $callable;
    }

    /**
     * Returns handler for given shortcode name or default if it was set before.
     * If no handler is found, returns null.
     *
     * @param string $name
     * @return callable|null
     */
    public function get($name)
    {
        if (array_key_exists($name, $this->handlers)) {
            return $this->handlers[$name];
        }

        if ($this->hasHandlerClass($name)) {
            $klass = $this->getHandlerClassName($name);

            return App::make($klass);
        }

        return null;
    }

    /**
     * Determine if a shortcode handler exists for a shortcode.
     *
     * @param string $name
     * @return bool
     */
    public function has($name): bool
    {
        return array_key_exists($name, $this->handlers) || $this->hasHandlerClass($name);
    }

    /**
     * Determine if a handler class exists for a shortcode.
     *
     * @param string $name
     * @return bool
     */
    private function hasHandlerClass($name): bool
    {
        return class_exists($this->getHandlerClassName($name));
    }

    /**
     * Determine if a handler class exists for a shortcode.
     *
     * @param string $name
     * @return string
     */
    private function getHandlerClassName($name): string
    {
        return __NAMESPACE__ . '\\Shortcodes\\' . Str::studly($name) . 'Shortcode';
    }

    /**
     * Render shortcodes.
     *
     * @param string $content
     * @return string
     */
    public function render($content): string
    {
        $processor = new Processor(new RegularParser, $this);
        $processor->withRecursionDepth(10);

        return (string) $processor->process($content);
    }

    /**
     * Helper that provides output buffering for the template helper.
     *
     * @param \Closure $closure
     * @return string
     */
    protected function capture(Closure $closure): string
    {
        ob_start();
        $closure();

        return ob_get_clean();
    }
}
