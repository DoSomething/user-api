<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'signup_id',
        'northstar_id',
        'admin_northstar_id',
        'status',
        'old_status',
        'comment',
        'post_id',
    ];

    /**
     * Each review has events.
     */
    public function events()
    {
        return $this->morphMany(Event::class, 'eventable');
    }

    /**
     * Each review belongs to a post.
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
