<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Action extends Model
{
    /**
     * The database connection that should be used by the model.
     *
     * @var string
     */
    protected $connection = 'mysql';

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'reportback' => 'boolean',
        'civic_action' => 'boolean',
        'scholarship_entry' => 'boolean',
        'anonymous' => 'boolean',
        'online' => 'boolean',
        'quiz' => 'boolean',
        'collect_school_id' => 'boolean',
        'volunteer_credit' => 'boolean',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'campaign_id',
        'post_type',
        'action_type',
        'time_commitment',
        'callpower_campaign_id',
        'reportback',
        'civic_action',
        'scholarship_entry',
        'anonymous',
        'online',
        'quiz',
        'impact_goal',
        'noun',
        'verb',
        'collect_school_id',
        'volunteer_credit',
    ];

    /**
     * The relationships that should be touched on save.
     *
     * @var array
     */
    protected $touches = ['campaign'];

    /**
     * Attributes that can be queried when filtering.
     *
     * This array is manually maintained. It does not necessarily mean that
     * any of these are actual indexes on the database... but they should be!
     *
     * @var array
     */
    public static $indexes = ['id', 'campaign_id', 'callpower_campaign_id'];

    /**
     * Get any fields on this model that are casted to a boolean.
     *
     * @return array
     */
    public static function getBooleans()
    {
        return array_keys((new self())->casts, 'boolean');
    }

    /**
     * Each action belongs to a campaign.
     */
    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * An action has many posts.
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }
}
