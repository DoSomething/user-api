<?php

namespace App\Models;

/**
 * The OAuth refresh token model.
 *
 * @property string $id
 * @property string $_id
 * @property string $token
 * @property string $user_id
 *
 * @method static \Illuminate\Database\Eloquent\Builder where(string $field, string $comparison = '=', string $value)
 */
class RefreshToken extends MongoModel
{
    /**
     * The database collection used by the model.
     *
     * @var string
     */
    protected $collection = 'refresh_tokens';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
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
