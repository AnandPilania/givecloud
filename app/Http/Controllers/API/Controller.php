<?php

namespace Ds\Http\Controllers\API;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;

    /**
     * Create an instance.
     */
    public function __construct()
    {
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
     * Get the URL for an API request.
     *
     * @param string $path
     * @return string
     */
    protected function url($path)
    {
        return url('jpanel/api/v1/' . ltrim($path, '/'));
    }
}
