<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Reaction extends Model
{
    use SoftDeletes;

    /**
     * The database connection that should be used by the model.
     *
     * @var string
     */
    protected $connection = 'mysql';

    /**
     * All of the relationships to be touched.
     *
     * @var array
     */
    protected $touches = ['post'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['northstar_id', 'post_id'];

    /**
     * Each reaction belongs to a post.
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}
