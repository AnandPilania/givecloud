<?php

namespace Ds\Http\Controllers;

use Ds\Domain\Messenger\Models\Conversation;
use Ds\Domain\Messenger\Models\ConversationRecipient;

class ConversationController extends Controller
{
    /**
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function showConversations()
    {
        return view('conversations.index', [
            '__menu' => 'products.conversations',
            'conversations' => Conversation::with('recipients')->get(),
            'recipients' => ConversationRecipient::all(),
        ]);
    }

    /**
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function addConversation()
    {
        return $this->showConversation(new Conversation([
            'enabled' => false,
            'conversation_type' => key(Conversation::getConversationTypes()->toArray()),
        ]));
    }

    /**
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function showConversation(Conversation $conversation)
    {
        $conversationTypes = Conversation::getConversationTypes()->each(function ($conversationType, $key) use ($conversation) {
            ob_start();
            gc_metadata_schema($conversationType->settings, $conversation->metadata);
            $conversationType->metadata_html = trim(ob_get_clean());
            $conversationType->id = $key;
        });

        return view('conversations.view', [
            '__menu' => 'products.conversations',
            'conversation' => $conversation,
            'conversationTypes' => $conversationTypes,
            'recipients' => ConversationRecipient::all(),
        ]);
    }

    /**
     * @return \Illuminate\Http\Response|array
     */
    public function saveConversation()
    {
        $command = strtolower((string) request('command'));
        $recipients = collect(request('recipients'))->pluck('id');

        $intersects = Conversation::with('recipients')->get()
            ->reject(function ($conversation) {
                return $conversation->id == request('id');
            })->filter(function ($conversation) use ($command) {
                return $conversation->command === $command;
            })->filter(function ($conversation) use ($recipients) {
                // no recipients means ALL recipients and therefore
                // there is an intersection between the two
                if ($recipients->isEmpty() || $conversation->recipients->isEmpty()) {
                    return true;
                }

                return $conversation->recipients->pluck('id')->intersect($recipients)->isNotEmpty();
            })->isNotEmpty();

        if ($intersects) {
            return response(['error' => 'Recipient is already assigned to a similar conversation.'], 500);
        }

        // https://support.twilio.com/hc/en-us/articles/223134027-Twilio-support-for-opt-out-keywords-SMS-STOP-filtering-
        if (in_array($command, ['cancel', 'end', 'help', 'info', 'quit', 'start', 'stop', 'stopall', 'unstop', 'unsubscribe'])) {
            return response(['error' => strtoupper($command) . ' is an opt-in/out keyword reserved for compliance with industry rules and regulations for opt-out handling.'], 500);
        }

        $conversation = Conversation::findOrNew(request('id'));

        $conversation->fill([
            'enabled' => request('enabled', false),
            'command' => $command,
            'conversation_type' => request('conversation_type'),
            'tracking_source' => request('tracking_source'),
        ]);

        if ($metadata = request('metadata')) {
            $conversation->metadata($metadata);
        }

        $conversation->save();

        if (count($recipients)) {
            $conversation->recipients()->sync($recipients);
        } else {
            $conversation->recipients()->detach();
        }

        return compact('conversation');
    }
}
