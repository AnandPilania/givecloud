<?php

namespace Ds\Services;

use Ds\Domain\Shared\Exceptions\EmailNotProvidedException;
use Ds\Models\Member;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

class SupporterSocialLoginService extends SocialLoginService
{
    public function fromIncomingUser(string $provider): ?Authenticatable
    {
        $socialUser = $this->incomingUser($provider);

        if (! $socialUser->getEmail()) {
            throw new EmailNotProvidedException;
        }

        $firstName = $socialUser['given_name'] ?? $socialUser['givenName'] ?? $socialUser['first_name'] ?? '';
        $lastName = $socialUser['family_name'] ?? $socialUser['surname'] ?? $socialUser['last_name'] ?? '';

        $member = Member::firstOrCreate(
            ['email' => $socialUser->getEmail()],
            [
                'first_name' => $firstName,
                'last_name' => $lastName,
            ]
        );

        return tap($member, function (Member $member) use ($socialUser, $firstName, $lastName) {
            $country = app('geoip')->get('iso_code', request()->ip());
            $city = app('geoip')->get('city', request()->ip());
            $state = app('geoip')->get('state', request()->ip());
            $postalCode = app('geoip')->get('postal_code', request()->ip());

            $member->first_name = $member->first_name ?: $firstName;
            $member->last_name = $member->last_name ?: $lastName;

            $member->bill_first_name = $member->bill_first_name ?: $firstName ?? '';
            $member->bill_last_name = $member->bill_last_name ?: $lastName ?? '';
            $member->bill_email = $member->bill_email ?: $socialUser->getEmail();
            $member->bill_state = $member->bill_state ?: $state;
            $member->bill_zip = $member->bill_zip ?: $postalCode;
            $member->bill_city = $member->bill_city ?: $city;
            $member->bill_country = $member->bill_country ?: $country;

            $member->ship_first_name = $member->ship_first_name ?: $firstName ?? '';
            $member->ship_last_name = $member->ship_last_name ?: $lastName;
            $member->ship_email = $member->ship_email ?: $socialUser->getEmail();
            $member->ship_state = $member->ship_state ?: $state;
            $member->ship_zip = $member->ship_zip ?: $postalCode;
            $member->ship_city = $member->ship_city ?: $city;
            $member->ship_country = $member->ship_country ?: $country;

            $member->save();
        });
    }

    public function login(Authenticatable $user, $provider): void
    {
        $user->socialIdentities()
            ->ofProvider($provider)
            ->touch();

        member_login_with_id($user->id);
    }

    public function incomingUser(string $provider): SocialiteUser
    {
        if (static::$incomingUser) {
            return static::$incomingUser;
        }

        $driver = Socialite::driver($provider)
            ->stateless()
            ->redirectUrl(config("services.$provider.supporter_redirect"));

        if ($provider === 'facebook') {
            $driver->fields(['name', 'email', 'gender', 'verified', 'link', 'first_name', 'last_name']);
        }

        return static::$incomingUser = $driver->user();
    }

    public function revokeConnection(string $provider)
    {
        if ($provider !== 'facebook') {
            return;
        }

        $socialUser = $this->incomingUser($provider);

        Http::delete('https://graph.facebook.com/v3.0/' . $socialUser->getId() . '/permissions', [
            'access_token' => $socialUser->token,
        ]);
    }
}
