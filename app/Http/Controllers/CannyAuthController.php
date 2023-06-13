<?php

namespace Ds\Http\Controllers;

use Firebase\JWT\JWT;
use Illuminate\Http\Request;
use Spatie\Url\Url;

class CannyAuthController extends Controller
{
    /** @return \Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse */
    public function __invoke(Request $request)
    {
        if ($request->wantsJson()) {
            return response()->json(['sso_token' => $this->generateSsoToken()]);
        }

        $redirectTo = (string) Url::fromString('https://canny.io/api/redirects/sso')
            ->withQueryParameter('companyID', config('services.canny.company_id'))
            ->withQueryParameter('ssoToken', $this->generateSsoToken())
            ->withQueryParameter('redirect', config('services.canny.redirect_url'));

        return redirect($redirectTo);
    }

    private function generateSsoToken(): string
    {
        $site = site();
        $user = user();

        $data = [
            // use email as ID to prevent duplicates for individuals
            // that have user accounts on multiple Givecloud sites
            'id' => sha1($user->email),

            'name' => $user->full_name,
            'email' => $user->email,
            'avatarURL' => null,
            'created' => toUtcFormat($user->created_at, 'api'),
            'companies' => [
                'id' => $site->client->id,
                'name' => $site->client->name,
                'monthlySpend' => $site->client->subscription->mrr ?? null,
                'created' => toUtcFormat($site->client->created_at, 'api'),
            ],
        ];

        return JWT::encode($data, config('services.canny.private_key'), 'HS256');
    }
}
