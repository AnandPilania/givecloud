<?php

namespace Ds\Services;

use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Models\Passport\Token;
use Ds\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Laravel\Passport\Passport;
use Laravel\Passport\PersonalAccessTokenResult;
use Throwable;

class PersonalAccessTokenService
{
    /**
     * @throws \Ds\Domain\Shared\Exceptions\MessageException
     */
    public function create(User $user, string $name): PersonalAccessTokenResult
    {
        try {
            return $user->createToken(strip_tags($name));
        } catch (Throwable $e) {
            report($e);
            throw new MessageException("An error occured while creating $name personal access token.");
        }
    }

    public function getAllForUser(int $userId): Collection
    {
        return Passport::token()
            ->where('user_id', $userId)
            ->where('revoked', false)
            ->where('expires_at', '>', now())
            ->get();
    }

    public function revoke(Token $token): bool
    {
        return $token->revoked ? true : $token->revoke();
    }
}
