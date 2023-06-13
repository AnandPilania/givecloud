<?php

namespace Ds\Http\Middleware;

use Closure;
use Ds\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cookie;
use Spatie\Url\Url;
use Throwable;

class CheckReferral
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (! $request->hasCookie('gcr') && $request->query('gcr')) {
            try {
                $referral_member_id = app('hashids')->decode($request->query('gcr'));
                if (count($referral_member_id) === 1) {
                    $referral_member_id = (int) Arr::get($referral_member_id, 0);
                    if (Member::where('id', $referral_member_id)->exists()) {
                        Cookie::queue('gcr', $referral_member_id, 3600 * 24 * 7);
                    }
                }
            } catch (Throwable $e) {
                // do nothing
            }

            return redirect()->to($this->getUrlWithoutReferralCode($request));
        }

        return $next($request);
    }

    private function getUrlWithoutReferralCode(Request $request)
    {
        return (string) Url::fromString($request->fullUrl())
            ->withoutQueryParameter('gcr');
    }
}
