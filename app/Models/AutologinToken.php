<?php

namespace Ds\Models;

use Ds\Domain\Shared\Exceptions\MessageException;
use Ds\Illuminate\Auth\Autologinable;
use Ds\Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class AutologinToken extends Model
{
    /**
     * The token.
     *
     *   The actual tokens are not stored anywhere! They are
     *   stored and compared as hashes. The only place the original
     *   token will exist is in this variable after the token
     *   is first created or refreshed.
     *
     * @var string
     */
    public $token;

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'hits' => 'integer',
        'expires' => 'datetime',
        'kamikaze' => 'boolean',
    ];

    public function user(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->token)) {
                $model->generateToken();
            }
        });
    }

    /**
     * Scope: Active
     *
     * @param \Illuminate\Database\Query\Builder $query
     */
    public function scopeActive($query)
    {
        $query->whereNull('expires');
        $query->orWhere('expires', '>', fromUtc('now'));
    }

    /**
     * Scope: Token
     *
     * @param \Illuminate\Database\Query\Builder $query
     */
    public function scopeToken($query, $token)
    {
        $query->where('hashed_token', sha1($token));
    }

    /**
     * Attribute Accessor: URL
     *
     * @return string
     *
     * @throws \Ds\Domain\Shared\Exceptions\MessageException
     */
    public function getUrlAttribute()
    {
        if (empty($this->token)) {
            if ($this->exists) {
                throw new MessageException('The URL for an autologin token is only available when it is first created.');
            }

            $this->generateToken();
            $this->save();
        }

        return route('autologin', [$this->token]);
    }

    /**
     * Generate the token.
     *
     * @throws \Ds\Domain\Shared\Exceptions\MessageException
     */
    public function generateToken()
    {
        if ($this->exists) {
            throw new MessageException('The token can not be changed once created.');
        }

        do {
            $this->token = Str::random(12);
        } while (static::token($this->token)->count());

        $this->hashed_token = sha1($this->token);
    }

    /**
     * Consume the autologin token.
     */
    public function consumeToken()
    {
        $this->user->autologin();

        if ($this->kamikaze) {
            $this->delete();
        } else {
            $this->hits++;
            $this->save();
        }
    }

    /**
     * Generate an autologin URL.
     *
     * @param \Ds\Illuminate\Auth\Autologinable $user
     * @param array $options
     * @return string
     */
    public static function make(Autologinable $user, array $options = [])
    {
        $token = new static;
        $token->user_type = $user->getMorphClass();
        $token->user_id = $user->getKey();
        $token->path = Arr::get($options, 'path');
        $token->kamikaze = Arr::get($options, 'kamikaze', false);
        $token->expires = fromUtc(Arr::get($options, 'expires'));
        $token->save();

        return $token->url;
    }
}
