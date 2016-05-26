<?php

namespace Northstar\Models;

use Illuminate\Support\Str;
use Jenssegers\Mongodb\Eloquent\Model;

/**
 * The Client model. These identify the "client application" making
 * a request, and their maximum allowed scopes.
 *
 * @property string client_id
 * @property string client_secret
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
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

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
        'scope',

        // For backwards compatibility...
        'app_id',
        'api_key',
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

        // Automatically set random API key. This field *may* be manually
        // set when seeding the database, so we first check if empty.
        static::creating(function (Client $client) {
            if (empty($client->client_secret)) {
                $client->client_secret = Str::random(32);
            }
        });
    }

    /**
     * Map legacy 'app_id' to it's OAuth equivalent.
     * @return string
     */
    public function setAppIdAttribute($value)
    {
        $this->attributes['client_id'] = snake_case(str_replace(' ', '', $value));
    }

    /**
     * Mutator for 'client_id' attribute.
     * @return string
     */
    public function setClientIdAttribute($value)
    {
        $this->attributes['client_id'] = snake_case(str_replace(' ', '', $value));
    }

    /**
     * Mutator for 'scope' attribute.
     * @return array
     */
    public function getScopeAttribute()
    {
        if (! isset($this->attributes['scope']) || ! is_array($this->attributes['scope'])) {
            return [];
        }

        return $this->attributes['scope'];
    }

    /**
     * Check if this API key has the given scope.
     *
     * @param $scope - Scope to test for
     * @return bool
     */
    public function hasScope($scope)
    {
        return in_array($scope, $this->scope);
    }

    /**
     * Get the API key specified for the current request.
     *
     * @return \Northstar\Models\Client
     */
    public static function current()
    {
        $client_secret = request()->header('X-DS-REST-API-Key');

        return static::where('client_secret', $client_secret)->first();
    }
}
