<?php

namespace App\Models;

use Carbon\Carbon;

/**
 * The OAuth refresh token model.
 *
 * @property string $id
 * @property string $code
 * @property string $token
 * @property array $scopes
 * @property Carbon $expiration
 * @property string $user_id
 * @property string $client_id
 *
 * @method static \Illuminate\Database\Eloquent\Builder where(string $field, string $comparison = '=', string $value)
 */
class AuthCode extends Model
{
    /**
     * The database collection used by the model.
     *
     * @var string
     */
    protected $collection = 'auth_codes';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'expiration' => 'date',
        'scopes' => 'array',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'code',
        'scopes',
        'user_id',
        'client_id',
        'expiration',
        'redirect_uri',
    ];
}
