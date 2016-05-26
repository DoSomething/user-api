<?php

namespace Northstar\Models;

use Jenssegers\Mongodb\Eloquent\Model;

/**
 * The OAuth refresh token model.
 *
 * @property string $id
 * @property string $_id
 * @property string $token
 * @property string $user_id
 */
class RefreshToken extends Model
{
    /**
     * The database collection used by the model.
     *
     * @var string
     */
    protected $collection = 'refresh_tokens';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    protected $casts = [
        'scopes' => 'array',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['token', 'scopes', 'user_id', 'client_id'];
}
