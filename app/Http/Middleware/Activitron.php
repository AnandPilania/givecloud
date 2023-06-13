<?php

namespace Ds\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class Activitron
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->shouldLogQueries($request)) {
            dbq()->logQueries($request, storage_path('logs/queries.log'));
        }

        return $next($request);
    }

    /**
     * Log queries from a given IP for 10 minutes after the
     * setting was enabled.
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    private function shouldLogQueries(Request $request)
    {
        if (sys_get('log_queries_for_ip') !== $request->ip()) {
            return false;
        }

        if (sys_get()->modified('log_queries_for_ip')->diffInMinutes() > 10) {
            return false;
        }

        return true;
    }
}
