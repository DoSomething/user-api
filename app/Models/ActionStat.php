<?php

namespace App\Models;

use App\Models\Traits\HasCursor;
use Illuminate\Database\Eloquent\Model;

class ActionStat extends Model
{
    use HasCursor;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['action_id', 'impact', 'location', 'school_id'];

    /**
     * Attributes that can be queried when filtering.
     *
     * This array is manually maintained. It does not necessarily mean that
     * any of these are actual indexes on the database... but they should be!
     *
     * @var array
     */
    public static $indexes = ['action_id', 'location', 'school_id'];

    /**
     * Attributes that can be sorted by.
     *
     * This array is manually maintained. It does not necessarily mean that
     * any of these are actual indexes on the database... but they should be!
     *
     * @var array
     */
    public static $sortable = ['id', 'impact'];

    /**
     * Scope a query to only include schools that have groups in given group type ID.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $groupTypeId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInGroupTypeId($query, $groupTypeId)
    {
        return $query
            ->join('groups', 'action_stats.school_id', '=', 'groups.school_id')
            ->where('groups.group_type_id', '=', $groupTypeId);
    }
}
