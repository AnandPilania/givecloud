<?php

namespace Ds\Domain\HotGlue\Transformers\Mailchimp;

use Ds\Models\Member;
use League\Fractal\TransformerAbstract;

class AccountTransformer extends TransformerAbstract
{
    public function transform(Member $account): array
    {
        return [
            'name' => $account->display_name,
            'email' => $account->email,
            'subscribe_status' => $account->email_opt_in ? 'subscribed' : 'unsubscribed',
        ];
    }
}
