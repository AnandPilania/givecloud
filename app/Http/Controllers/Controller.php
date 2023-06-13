<?php

namespace Ds\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Str;

class Controller extends BaseController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;

    /** @var \Ds\Services\FlashService */
    public $flash;

    /** @var string */
    protected $viewLayout = 'layouts.app';

    /**
     * Create an instance.
     */
    public function __construct()
    {
        $this->flash = app('flash');

        $this->registerMiddleware();
    }

    /**
     * Register controller middleware.
     */
    protected function registerMiddleware()
    {
        $this->middleware('auth');
    }

    /**
     * Render template.
     *
     * @param string|int $templateName
     * @param array $assigns
     * @return string
     */
    public function renderTemplate($templateName, array $assigns = [])
    {
        cart()->updateUtmTracking();

        $theme = null;
        $templateName = rtrim((string) $templateName, '.');

        if (Str::startsWith($templateName, '~')) {
            $theme = app('theme')->theme('givecloud');
            $templateName = Str::after($templateName, '~');
        }

        $template = new \Ds\Domain\Theming\Liquid\Template("templates/$templateName", null, $theme);

        return $template->render(reqcache('render_template_assigns', $assigns));
    }

    /**
     * Add variables to view and optionally pick template.
     *
     * @param string|bool $template
     * @param array $vars
     * @return \Illuminate\View\View
     */
    public function getView($template = false, array $vars = [])
    {
        if (empty($this->viewLayout)) {
            return view($template, $vars);
        }

        return tap(view($this->viewLayout, $vars), function ($view) use ($template, $vars) {
            $view->getFactory()->startSection('content', view($template, $vars));
        });
    }

    /**
     * Set the layout to be using with the view.
     *
     * @param string|bool|null $layout
     */
    public function setViewLayout($layout)
    {
        if (is_bool($layout)) {
            $layout = $layout ? 'index' : null;
        }

        $this->viewLayout = $layout;
    }

    /**
     * Execute an action on the controller.
     *
     * @param string $method
     * @param array $parameters
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function callAction($method, $parameters)
    {
        // Capture the current error reporting level so that
        // it can be restored after processing the action
        $level = error_reporting();

        error_reporting(E_ALL ^ E_NOTICE);

        $content = parent::callAction($method, $parameters);

        // Restore the error reporting level
        error_reporting($level);

        return $content;
    }
}
