<?php

namespace Northstar\Http\Transformers;

use League\Fractal\Scope;
use Northstar\Models\User;
use Illuminate\Support\Str;
use League\Fractal\TransformerAbstract;

class BaseTransformer extends TransformerAbstract
{
    /**
     * Resources that can be included if requested.
     *
     * @return array
     */
    public function getAvailableIncludes()
    {
        return static::$model::$sensitive;
    }

    /**
     * Set the resources that will be included by default.
     *
     * @return array
     */
    public function getDefaultIncludes()
    {
        // If we've enabled the "optional fields" feature flag, then any fields
        // containing sensitive information must be explicitly requested:
        if (config('features.optional_fields')) {
            return [];
        }

        // Until then, include everything!
        return $this->getAvailableIncludes();
    }

    /**
     * Load any resources specified by the `?include=` query parameter and
     * automatically register `includeFieldName` handlers for optional
     * fields on the User model, with authorization & logging.
     *
     * @param Scope  $scope
     * @param string $attribute
     * @param mixed  $resource
     *
     * @throws \Exception
     *
     * @return \League\Fractal\Resource\ResourceInterface
     */
    protected function callIncludeMethod(Scope $scope, $attribute, $resource)
    {
        // Is this a sensitive attribute? If so, check authorization & log access:
        if (in_array($attribute, static::$model::$sensitive)) {
            if (! $this->authorize($resource, $attribute)) {
                return null;
            }

            // @TODO: Log access to this optional field.
            // ...
        }

        // If we don't have a custom "include" method, try default resolver:
        if (! method_exists($this, 'include'.Str::studly($attribute))) {
            return $this->primitive($resource->{$attribute});
        }

        return parent::callIncludeMethod($scope, $attribute, $resource);
    }
}
