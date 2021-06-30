<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    /**
     * The database connection that should be used by the model.
     *
     * @var string
     */
    protected $connection = 'mysql';

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
     * Each review belongs to a post.
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
