<?php

namespace Ds\Domain\Messenger\Models;

use Ds\Domain\Messenger\BotMan;
use Ds\Eloquent\HasMetadata;
use Ds\Eloquent\Metadatable;
use Ds\Illuminate\Database\Eloquent\Factories\HasFactory;
use Ds\Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

class Conversation extends Model implements Metadatable
{
    use HasFactory;
    use HasMetadata;
    use SoftDeletes;

    /** @var array */
    protected static $conversationTypes = [
        'donate_amount' => \Ds\Domain\Messenger\Conversations\DonateAmountConversation::class,
        'donate_amount_fundraising_page' => \Ds\Domain\Messenger\Conversations\DonateAmountFundraisingPageConversation::class,
        'pledge_amount' => \Ds\Domain\Messenger\Conversations\PledgeAmountConversation::class,
        'reply' => \Ds\Domain\Messenger\Conversations\ReplyConversation::class,
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'enabled' => 'boolean',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'enabled',
        'command',
        'conversation_type',
        'tracking_source',
    ];

    /**
     * Relationship: Recipients
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function recipients()
    {
        return $this->belongsToMany(ConversationRecipient::class, 'conversations_pivot');
    }

    /**
     * Get all the conversation types and their configurations.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getConversationTypes(): Collection
    {
        $conversationTypes = new Collection;

        foreach (static::$conversationTypes as $conversationType => $klass) {
            $conversationTypes[$conversationType] = (object) $klass::configuration();
        }

        return $conversationTypes;
    }

    /**
     * Get the conversation type class.
     *
     * @return string
     */
    public function getConversationTypeClass()
    {
        return Arr::get(static::$conversationTypes, $this->conversation_type);
    }

    /**
     * Register the conversations command with BotMan.
     *
     * @param \Ds\Domain\Messenger\BotMan $botman
     */
    public function registerCommand(BotMan $botman)
    {
        $command = $this->prepareCommand($this->command);

        if ($this->recipients->isEmpty()) {
            $botman->hears($command, function ($bot, ...$parameters) {
                $this->startConversation($bot, $parameters);
            });
        } else {
            $botman->group(['recipient' => $this->recipients->pluck('identifier')->all()], function ($bot) use ($command) {
                $bot->hears($command, function ($bot, ...$parameters) {
                    $this->startConversation($bot, $parameters);
                });
            });
        }
    }

    private function prepareCommand(string $command): string
    {
        $command = preg_quote($command);
        $command = preg_replace('/\\\\{([a-z]+)\\\\}/im', '{$1}', $command);

        $parameters = call_user_func([
            $this->getConversationTypeClass(),
            'configuration',
        ])['parameters'] ?? [];

        foreach ($parameters as $key => $value) {
            $command = str_replace(sprintf('{%s}', $key), "(?<$key>$value)", $command);
        }

        return $command;
    }

    /**
     * Starts a BotMan conversation.
     *
     * @param \Ds\Domain\Messenger\BotMan $bot
     * @param array $parameters
     */
    private function startConversation(BotMan $bot, array $parameters)
    {
        $klass = $this->getConversationTypeClass();
        $parameters = $this->getParameters($parameters);

        $bot->startConversation(new $klass($this, $parameters));
    }

    /**
     * Combines the parameters from the command with the parameters
     * provided when BotMan starts the conversation.
     *
     * @param array $values
     * @return array
     */
    private function getParameters(array $values)
    {
        preg_match_all('/(?<={)[a-z]+(?=})/im', $this->command, $keys);
        $keys = $keys[0];

        $values = array_pad($values, count($keys), null);
        $values = array_slice($values, 0, count($keys));

        return array_combine($keys, $values);
    }
}
