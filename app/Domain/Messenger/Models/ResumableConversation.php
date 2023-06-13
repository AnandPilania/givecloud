<?php

namespace Ds\Domain\Messenger\Models;

use Carbon\Carbon;
use DateTimeInterface;
use Ds\Domain\Messenger\BotMan;
use Ds\Domain\Messenger\ResumableMessage;
use Ds\Eloquent\Hashids;
use Ds\Illuminate\Database\Eloquent\Model;
use Ds\Models\Member as Account;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\InteractsWithTime;

class ResumableConversation extends Model
{
    use InteractsWithTime;
    use Hashids;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'conversation_id' => 'integer',
        'parameters' => 'array',
        'account_id' => 'integer',
        'expires' => 'datetime',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'driver',
        'sender',
        'recipient',
        'message',
        'conversation_id',
        'parameters',
        'account_id',
        'resume_on',
        'expires',
    ];

    /**
     * Relationship: Account
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Relationship: Conversation
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function conversation()
    {
        return $this->belongsTo(Conversation::class);
    }

    /**
     * Attribute Mutator: Expires
     *
     * @param mixed $value
     */
    public function setExpiresAttribute($value)
    {
        $value = $this->getMinutes($value);

        $this->attributes['expires'] = Carbon::now()->addMinutes($value);
    }

    /**
     * Attribute Accessor: Permalink
     *
     * @return string
     */
    public function getPermalinkAttribute()
    {
        return URL::routeAsShortlink('sms', [$this->hashid]);
    }

    /**
     * Resume/restart the original conversation.
     */
    public function resume()
    {
        $bot = app(BotMan::class);
        $bot->loadDriver($this->driver);

        $bot->setMessage(new ResumableMessage(
            $this->message,
            $this->sender,
            $this->recipient
        ));

        $klass = $this->conversation->getConversationTypeClass();
        $instance = new $klass($this->conversation, $this->parameters);

        if ($this->account) {
            $instance->setAccount($this->account);
        }

        // Some BotMan drivers output responses directly so we
        // need to catch and discard any output and discard
        ob_start();

        $instance->setBot($bot);
        $instance->run();

        if (method_exists($bot->getDriver(), 'messagesHandled')) {
            $bot->getDriver()->messagesHandled();
        }

        ob_end_clean();

        $this->delete();
    }

    /**
     * Calculate the number of minutes with the given duration.
     *
     * @param \DateTimeInterface|\DateInterval|float|int $duration
     * @return float|int|null
     */
    protected function getMinutes($duration)
    {
        $duration = $this->parseDateInterval($duration);

        if ($duration instanceof DateTimeInterface) {
            $duration = Carbon::now()->diffInSeconds(Carbon::createFromTimestamp($duration->getTimestamp()), false) / 60;
        }

        return (int) ($duration * 60) > 0 ? $duration : null;
    }

    /**
     * Get a Hashids instance.
     *
     * @return \Hashids\Hashids
     */
    protected static function getHashids()
    {
        return new \Hashids\Hashids('HSvaUL4GYxceKWVogund', 11);
    }
}
