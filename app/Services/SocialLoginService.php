<?php

namespace Ds\Services;

use Ds\Mail\SocialLoginConfirmation;
use Ds\Models\SocialIdentity;
use Ds\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

class SocialLoginService
{
    public const CACHE_TTL_IN_MINUTES = 15;

    /** @var \SocialiteProviders\Manager\OAuth2\User */
    protected static $incomingUser;

    public function confirmSocialIdentity(string $provider, Authenticatable $user): SocialIdentity
    {
        $identity = $user->socialIdentities()->ofProvider($provider)->firstOrFail();
        $identity->is_confirmed = true;
        $identity->save();

        return $identity;
    }

    public function findOrCreateSocialIdentity(string $provider): SocialIdentity
    {
        $socialUser = $this->incomingUser($provider);
        $user = $this->fromIncomingUser($provider);

        /**
         * SocialiteProviders\Microsoft\MicrosoftUser throws error if user has no avatar.
         * Waiting for this commit to be tagged.
         * https://github.com/SocialiteProviders/Microsoft/commit/64a1f123b9f4059926c221680f197db5ae4fe150
         */
        $avatar = rescueQuietly(fn () => $socialUser->getAvatar());

        /*
         * Facebook deprecated its tokenless access to User Pictures. No point of saving it here
         * https://developers.facebook.com/blog/post/2020/08/04/Introducing-graph-v8-marketing-api-v8
         */
        if ($provider === 'facebook') {
            $avatar = null;
        }

        return $user->socialIdentities()->updateOrCreate([
            'provider_name' => $provider,
            'provider_id' => $socialUser->getId(),
        ], ['avatar' => $avatar]);
    }

    public function fromIncomingUser(string $provider): ?Authenticatable
    {
        return User::where('email', $this->incomingUser($provider)->getEmail())->firstOrFail();
    }

    public function incomingUser(string $provider): SocialiteUser
    {
        if (static::$incomingUser) {
            return static::$incomingUser;
        }

        return static::$incomingUser = Socialite::driver($provider)->stateless()->user();
    }

    public function incomingUserIsConfirmed($provider): bool
    {
        try {
            $user = $this->fromIncomingUser($provider);
        } catch (ModelNotFoundException $e) {
            return false;
        }

        return $this->socialIdentityIsConfirmed($user, $provider);
    }

    public function loginIncomingUser($provider)
    {
        $this->login($this->fromIncomingUser($provider), $provider);
    }

    public function login(Authenticatable $user, $provider)
    {
        $user->socialIdentities()
            ->ofProvider($provider)
            ->confirmed()
            ->touch();

        auth()->login($user);
    }

    public function revoke(string $provider, Authenticatable $user): void
    {
        $user->socialIdentities()
            ->ofProvider($provider)
            ->delete();
    }

    public function socialIdentityIsConfirmed(Authenticatable $user, string $provider): bool
    {
        return $user->socialIdentities()
            ->ofProvider($provider)
            ->confirmed()
            ->exists();
    }

    public function sendConfirmation(Authenticatable $user, string $provider): void
    {
        $token = Str::random(32);

        cache()->put(
            sprintf('auth_session:%s', hash('sha256', $token)),
            $user->getAuthIdentifier(),
            now()->addMinutes(self::CACHE_TTL_IN_MINUTES),
        );

        $user->mail(new SocialLoginConfirmation($user, $token, $provider));
    }

    public function userFromToken($token)
    {
        $userId = cache()->pull(sprintf('auth_session:%s', hash('sha256', $token)));

        return User::find($userId);
    }
}
