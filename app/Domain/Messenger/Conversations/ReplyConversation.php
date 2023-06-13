<?php

namespace Ds\Domain\Messenger\Conversations;

use Ds\Common\DataAccess;
use Ds\Domain\Messenger\Conversation;
use Illuminate\Support\Str;

class ReplyConversation extends Conversation
{
    /**
     * Start the conversation
     */
    public function handle()
    {
        $reply = $this->getConversation()->metadata['reply'];
        $account = $this->getAccount();

        if (Str::contains($reply, '[[profile_link]]')) {
            if ($account) {
                $link = $account->getAutologinLink(null, 'account/profile');
            } else {
                $link = secure_site_url('account/register');
            }

            $reply = str_replace('[[profile_link]]', shortlink($link), $reply);
        }

        $this->say($reply);
    }

    /**
     * Get the configuration for the conversation.
     *
     * @return array
     */
    public static function configuration(): array
    {
        return [
            'label' => 'Send a reply',
            'example' => 'VOLUNTEERING',
            'parameters' => [],
            'settings' => DataAccess::collection([
                [
                    'type' => 'header',
                    'content' => 'Options',
                ], [
                    'type' => 'textarea',
                    'name' => 'reply',
                    'label' => 'Reply with',
                    'hint' => 'Available merge tags: [[profile_link]]',
                ],
            ]),
        ];
    }
}
