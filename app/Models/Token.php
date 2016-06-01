<?php

namespace Northstar\Models;

use Illuminate\Support\Str;
use Jenssegers\Mongodb\Eloquent\Model;

/**
 * The Authentication Token model. These are used to make
 * requests on behalf of an authenticated user account.
 *
 * @property string $id
 * @property string $_id
 * @property string $key
 * @property string $user_id
 */
class Token extends Model
{
    /**
     * The database collection used by the model.
     *
     * @var string
     */
    protected $collection = 'tokens';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * Create a new Token.
     *
     * @param  array  $attributes
     * @return Token
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // Automatically set random token key. This field *may* be manually
        // set when seeding the database, so we first check if empty.
        static::creating(function (Token $token) {
            if (empty($token->key)) {
                do {
                    $key = Str::random(32);
                } while (static::where('key', $key)->exists());

                $token->key = $key;
            }
        });
    }

    /**
     * A token belongs to a user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if this given token is associated with the given user.
     *
     * @param int   $user_id
     * @param Token $token
     * @return mixed
     */
    public static function isUserToken($user_id, $token)
    {
        return self::where('user_id', '=', $user_id)
            ->where('key', '=', $token)
            ->exists();
    }
}
