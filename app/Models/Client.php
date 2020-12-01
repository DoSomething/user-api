<?php

namespace App\Models;

use Illuminate\Support\Str;

/**
 * The Client model. These identify the "client application" making
 * a request, and their maximum allowed scopes.
 *
 * @property string $title
 * @property string $description
 * @property string $client_id
 * @property string $client_secret
 * @property string $allowed_grant
 * @property array $redirect_uri
 * @property array $scope
 */
class Client extends Model
{
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'client_id';

    /**
     * The database collection used by the model.
     *
     * @var string
     */
    protected $collection = 'clients';

    /**
     * The model's default attributes.
     *
     * @var array
     */
    protected $attributes = [
        'scope' => [],
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'client_id',
        'title',
        'description',
        'allowed_grant',
        'redirect_uri',
        'scope',
    ];

    /**
     * Create a new API key.
     *
     * @param $attributes
     * @return Client
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // Automatically set random client secret. This field *may* be manually
        // set when seeding the database, so we first check if empty.
        static::creating(function (self $client) {
            if (empty($client->client_secret)) {
                $client->client_secret = Str::random(32);
            }
        });
    }

    /*
     * Mutator for 'client_id' attribute.
     * @return string
     */
    public function setClientIdAttribute($value)
    {
        $this->attributes['client_id'] = Str::snake($value);
    }

    /**
     * Mutator for 'scope' attribute.
     * @return array
     */
    public function getScopeAttribute()
    {
        if (
            !isset($this->attributes['scope']) ||
            !is_array($this->attributes['scope'])
        ) {
            return [];
        }

        return $this->attributes['scope'];
    }

    /**
     * Get the number of refresh tokens assigned to this client.
     * @return array
     */
    public function getRefreshTokenCount()
    {
        return RefreshToken::where('client_id', $this->client_id)->count();
    }
}
