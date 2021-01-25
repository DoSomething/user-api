<?php

namespace App\Models;

use App\Models\Traits\HasCursor;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    use HasCursor;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'city',
        'goal',
        'group_type_id',
        'location',
        'name',
        'school_id',
    ];

    /**
     * Attributes that can be queried when filtering.
     *
     * This array is manually maintained. It does not necessarily mean that
     * any of these are actual indexes on the database... but they should be!
     *
     * @var array
     */
    public static $indexes = ['group_type_id', 'id', 'location', 'school_id'];

    /**
     * Attributes that we can sort by with the '?orderBy' query parameter.
     *
     * This array is manually maintained. It does not necessarily mean that
     * any of these are actual indexes on the database... but they should be!
     *
     * @var array
     */
    public static $sortable = ['name'];

    /**
     * Get the group type associated with this group.
     */
    public function group_type()
    {
        return $this->belongsTo(GroupType::class);
    }

    /**
     * Get the posts associated with this group.
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Get the signups associated with this group.
     */
    public function signups()
    {
        return $this->hasMany(Signup::class);
    }

    /**
     * Transform this group for Customer.io.
     *
     * @return array
     */
    public function toCustomerIoPayload()
    {
        return [
            // Note: These fields will be merged in with post & signup events, and so
            // it's important to prefix them all with 'group_' to avoid conflicts!
            'group_id' => $this->id,
            'group_name' => $this->name,
            'group_type_id' => $this->group_type->id,
            'group_type_name' => $this->group_type->name,
        ];
    }
}
