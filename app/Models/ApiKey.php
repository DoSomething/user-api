<?php

namespace Northstar\Models;

use Jenssegers\Mongodb\Model;

class ApiKey extends Model
{
    /**
     * The database collection used by the model.
     *
     * @var string
     */
    protected $collection = 'api_keys';

    /**
     * The model's default attributes.
     *
     * @var array
     */
    protected $attributes = [
        'scope' => ['user'],
    ];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = [
        'scope' => 'array',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'app_id',
        'scope',
    ];

    /**
     * Create a new API key.
     *
     * @param $attributes
     * @return ApiKey
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        // Automatically set random API key. This field *may* be manually
        // set when seeding the database, so we first check if empty.
        if (empty($this->api_key)) {
            $this->api_key = str_random(40);
        }
    }

    /**
     * Mutator for 'app_id' field.
     */
    public function setAppIdAttribute($app_id)
    {
        $this->attributes['app_id'] = snake_case(str_replace(' ', '', $app_id));
    }

    /**
     * Getter for 'scope' field.
     */
    public function getScopeAttribute()
    {
        if (empty($this->attributes['scope'])) {
            return ['user'];
        }

        $scope = $this->attributes['scope'];

        return is_string($scope) ? json_decode($scope) : $scope;
    }

    /**
     * Check if the key has the given scope.
     *
     * @param $scope
     * @return bool
     */
    public function hasScope($scope)
    {
        return in_array($scope, $this->scope);
    }

    /**
     * Get the API Key used on the current request.
     *
     * @return ApiKey|null
     */
    public static function current()
    {
        $app_id = request()->header('X-DS-Application-Id');
        $api_key = request()->header('X-DS-REST-API-Key');

        return static::get($app_id, $api_key);
    }

    /**
     * Get the API key with the given credentials.
     *
     * @return ApiKey|null
     */
    public static function get($app_id, $api_key)
    {
        return static::where('app_id', $app_id)->where('api_key', $api_key)->first();
    }

    /**
     * Check if the given App ID & key are valid.
     */
    public static function verify($app_id, $api_key)
    {
        return static::where('app_id', $app_id)->where('api_key', $api_key)->exists();
    }
}
