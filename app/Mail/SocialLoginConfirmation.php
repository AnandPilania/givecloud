<?php

namespace Ds\Mail;

use Ds\Models\User;
use Ds\Services\SocialLoginService;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SocialLoginConfirmation extends Mailable
{
    use Queueable;
    use SerializesModels;

    public string $provider;
    public string $token;
    public User $user;

    public function __construct(User $user, string $token, string $provider)
    {
        $this->user = $user;
        $this->token = $token;
        $this->provider = $provider;
    }

    public function build(): self
    {
        return $this->view('mailables.social-identity-confirmation')
            ->with([
                'url' => route('backend.socialite.confirm', [
                    'token' => $this->token,
                    'provider' => $this->provider,
                ]),
                'ttl' => SocialLoginService::CACHE_TTL_IN_MINUTES,
            ]);
    }
}
