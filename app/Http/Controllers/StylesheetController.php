<?php

namespace Ds\Http\Controllers;

class StylesheetController extends Controller
{
    /**
     * Register controller middleware.
     */
    protected function registerMiddleware()
    {
        // do nothing
    }

    public function asset($theme, $path)
    {
        return app('theme')->theme($theme)->asset($path)->toResponse();
    }
}
