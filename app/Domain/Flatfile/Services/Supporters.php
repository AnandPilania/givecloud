<?php

namespace Ds\Domain\Flatfile\Services;

use Ds\Models\AccountType;
use Ds\Models\Membership;
use Firebase\JWT\JWT;

class Supporters
{
    public function token(): string
    {
        return JWT::encode([
            'embed' => config('services.flatfile.embeds.supporters.id'),
            'user' => [
                'id' => auth()->user()->id,
                'email' => auth()->user()->email,
                'name' => auth()->user()->name,
            ],
            'org' => [
                'id' => site()->client->id,
                'name' => sys_get('ds_account_name'),
            ],
            'env' => [
                'account_name' => sys_get('ds_account_name'),
                'callback' => route('flatfile.webhook.supporters'),
                'memberships' => Membership::all()->pluck('name')->toArray(),
                'account_types' => AccountType::all()->pluck('name')->toArray(),
                'countries' => array_keys(cart_countries()),
            ],
        ], config('services.flatfile.embeds.supporters.key'), 'HS256');
    }
}
