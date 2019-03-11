<?php

namespace Northstar\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Jenssegers\Mongodb\Eloquent\Model as BaseModel;

/**
 * Base model class
 *
 * @mixin \Jenssegers\Mongodb\Query\Builder
 */
class Model extends BaseModel
{
    /**
     * Set a given attribute on the model.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return $this
     */
    public function setAttribute($key, $value)
    {
        parent::setAttribute($key, $value);

        // Empty strings should be saved as `null` when our attribute is a string.
        $booleanFields = ['email_subscription_status', 'sms_paused'];
        if (empty($this->attributes[$key]) && ! in_array($key, $booleanFields)) {
            $this->attributes[$key] = null;
        }

        $isDirty = $this->isDirty($key);
        $shouldAudit = ! in_array($key, ['updated_at', 'created_at']);

        if ($this->audited && $isDirty && $shouldAudit) {
            $this->attributes['audit'][$key] = [
                'source' => client_id(),
                'updated_at' => Carbon::now(),
            ];
        }

        return $this;
    }

    /**
     * Get the attributes that have been unset since the last sync.
     *
     * @return array
     */
    public function getClearable()
    {
        $clearable = [];

        foreach ($this->original as $key => $value) {
            // If a field was unset from the model's attributes or assigned null, mark it as clearable.
            if (! array_key_exists($key, $this->attributes) || is_null($this->attributes[$key])) {
                $clearable[$key] = null;
            }
        }

        return $clearable;
    }

    /**
     * Remove any null values from the model's attributes.
     *
     * @return void
     */
    public function removeNullAttributes()
    {
        $this->attributes = array_filter($this->attributes, function ($value) {
            return ! is_null($value);
        });
    }

    /**
     * Perform a model insert operation.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  array  $options
     * @return bool
     */
    protected function performInsert(Builder $query, array $options = [])
    {
        // Remove `null` values from the attributes before inserting.
        $this->removeNullAttributes();

        return parent::performInsert($query, $options);
    }

    /**
     * Perform a model update operation.
     * @see \Illuminate\Database\Eloquent\Model::performUpdate()
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  array  $options
     * @return bool
     */
    protected function performUpdate(Builder $query, array $options = [])
    {
        // Mark existing attributes that can be cleared, and remove any null values that
        // may have been added in this "update" operation.
        $clearable = $this->getClearable();
        $this->removeNullAttributes();

        $success = parent::performUpdate($query, $options);

        // If any attributes can be cleared, do so.
        if ($success && count($clearable) > 0) {
            $this->drop(array_keys($clearable));
        }

        return true;
    }
}
