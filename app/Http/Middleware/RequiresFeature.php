<?php

namespace Ds\Http\Middleware;

use Closure;

class RequiresFeature
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next, $feature)
    {
        if (feature($feature)) {
            return $next($request);
        }

        if ($request->is('jpanel/*')) {
            app('flash')->error("The <strong>$feature</strong> feature is not enabled.");

            return redirect()->to('jpanel');
        }

        abort(404);
    }
}
