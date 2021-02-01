<?php

namespace App\Models;

use App\Models\Traits\HasCursor;
use App\Services\GraphQL;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;

class Signup extends Model
{
    use SoftDeletes, HasCursor;

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'campaign_id' => 'string',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'campaign_id',
        'club_id',
        'created_at',
        'details',
        'group_id',
        'id',
        'northstar_id',
        'referrer_user_id',
        'source',
        'source_details',
        'updated_at',
        'why_participated',
    ];

    /**
     * Attributes that can be queried when filtering.
     *
     * This array is manually maintained. It does not necessarily mean that
     * any of these are actual indexes on the database... but they should be!
     *
     * @var array
     */
    public static $indexes = [
        'campaign_id',
        'club_id',
        'group_id',
        'id',
        'quantity',
        'northstar_id',
        'referrer_user_id',
        'source',
        'updated_at',
    ];

    /**
     * Attributes that we can sort by with the '?orderBy' query parameter. These
     * should not contain any sensitive or private data (as they'll be encoded
     * in cursors).
     *
     * This array is manually maintained. It does not necessarily mean that
     * any of these are actual indexes on the database... but they should be!
     *
     * @var array
     */
    public static $sortable = [
        'campaign_id',
        'updated_at',
        'northstar_id',
        'id',
        'quantity',
        'source',
    ];

    /**
     * Each signup belongs to a campaign.
     */
    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Each signup has events.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function events()
    {
        return $this->morphMany(Event::class, 'eventable');
    }

    /**
     * Get the group associated with this signup.
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Get the posts associated with this signup.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function posts()
    {
        return $this->hasMany(Post::class)->with('tags');
    }

    /**
     * Get the visible posts associated with this signup.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function visiblePosts()
    {
        $query = $this->hasMany(Post::class);

        if (!is_staff_user()) {
            $query->where(function ($query) {
                $query
                    ->where('status', 'accepted')
                    ->orWhere('northstar_id', auth()->id());
            });
        }

        return $query;
    }

    /**
     * Get the 'pending' posts associated with this signup.
     */
    public function pending()
    {
        return $this->hasMany(Post::class)
            ->where('status', '=', 'pending')
            ->with('tags');
    }

    /**
     * Get the 'accepted' posts associated with this signup.
     */
    public function accepted()
    {
        return $this->hasMany(Post::class)
            ->where('status', '=', 'accepted')
            ->with('tags');
    }

    /**
     * Get the 'rejected' posts associated with this signup.
     */
    public function rejected()
    {
        return $this->hasMany(Post::class)
            ->where('status', '=', 'rejected')
            ->with('tags');
    }

    /**
     * Scope a query to only include signups for a particular campaign.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCampaign($query, $ids)
    {
        return $query->wherein('campaign_id', $ids);
    }

    /**
     * Scope a query to include a count of post statuses for a signup.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeIncludePostStatusCounts($query)
    {
        return $query->withCount(['accepted', 'pending', 'rejected']);
    }

    /**
     * Transform this signup for Customer.io.
     *
     * @return array
     */
    public function toCustomerIoPayload()
    {
        // Fetch Campaign Website information via GraphQL.
        $campaignWebsite = app(GraphQL::class)->getCampaignWebsiteByCampaignId(
            $this->campaign_id,
        );

        $campaign = optional($this->campaign);

        return array_merge(
            [
                'id' => $this->id,
                'northstar_id' => $this->northstar_id,
                'campaign_id' => (string) $this->campaign_id,
                'campaign_run_id' => (string) $this->campaign_run_id,
                'campaign_title' => Arr::get($campaignWebsite, 'title'),
                'campaign_slug' => Arr::get($campaignWebsite, 'slug'),
                'campaign_cause' => implode(',', $campaign->cause ?: []),
                'quantity' => (int) $this->quantity,
                'why_participated' => strip_tags($this->why_participated),
                'source' => $this->source,
                'source_details' => $this->source_details,
                'referrer_user_id' => $this->referrer_user_id,
                'created_at' => optional($this->created_at)->timestamp,
                'updated_at' => optional($this->updated_at)->timestamp,
            ],
            optional($this->group)->toCustomerIoPayload() ?: [],
        );
    }

    /**
     * Calculate the total quantity for this signup.
     */
    public function refreshQuantity()
    {
        $this->quantity = $this->posts()->sum('quantity');
        $this->save();
    }

    /**
     * Get the quantity total associated with approved posts under this signup.
     *
     * @return int
     */
    public function getAcceptedQuantity()
    {
        $accepted_posts = $this->posts->where('status', 'accepted');

        return $accepted_posts->sum('quantity');
    }

    /**
     * Scope a query to only return signups if a user is an admin, staff, or is owner of signup and by type (optional).
     *
     * @param array $types
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithVisiblePosts($query, $types = null)
    {
        return $query->with([
            'visiblePosts' => function ($query) use ($types) {
                if ($types) {
                    $query->whereIn('type', $types);
                }
            },
        ]);
    }

    /**
     * Gets event payload for a referral signup, on behalf of the referrer user ID.
     */
    public function getReferralSignupEventPayload()
    {
        $userId = $this->northstar_id;
        $user = User::find($userId);

        $campaignWebsite = app(GraphQL::class)->getCampaignWebsiteByCampaignId(
            $this->campaign_id,
        );

        return [
            'id' => $this->id,
            'user_id' => $userId,
            'user_display_name' => $user->display_name,
            'campaign_id' => (string) $this->campaign_id,
            'campaign_title' => Arr::get($campaignWebsite, 'title'),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
