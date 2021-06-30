<?php

namespace App\Models;

use App\Models\Traits\HasCursor;
use App\Models\User;
use App\Notifications\PostTagged;
use App\Services\GraphQL;
use App\Types\BadgeType;
use Hashids\Hashids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Jenssegers\Mongodb\Eloquent\HybridRelations;

class Post extends Model
{
    use HasCursor, HybridRelations, Notifiable, SoftDeletes;

    /**
     * The database connection that should be used by the model.
     *
     * @var string
     */
    protected $connection = 'mysql';

    /**
     * Always load a user's own reaction,
     * if they're logged-in.
     */
    protected $with = ['reaction', 'tags', 'actionModel'];

    /**
     * The relationship counts that should be eager loaded on every query.
     *
     * @var array
     */
    protected $withCount = ['reactions'];

    /**
     * All of the relationships to be touched.
     *
     * @var array
     */
    protected $touches = ['signup'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'action',
        'action_id',
        'campaign_id',
        'club_id',
        'details',
        'group_id',
        'hours_spent',
        'id',
        'location',
        'postal_code',
        'quantity',
        'northstar_id',
        'referrer_user_id',
        'school_id',
        'signup_id',
        'source',
        'source_details',
        'status',
        'text',
        'type',
        'url',
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
        'action',
        'action_id',
        'campaign_id',
        'club_id',
        'created_at',
        'group_id',
        'id',
        'location',
        'northstar_id',
        'referrer_user_id',
        'signup_id',
        'source',
        'status',
        'type',
    ];

    /**
     * Attributes that we can sort by with the '?orderBy' query parameter.
     *
     * This array is manually maintained. It does not necessarily mean that
     * any of these are actual indexes on the database... but they should be!
     *
     * @var array
     */
    public static $sortable = ['created_at'];

    /**
     * The tags prefixed with 'good' that will send a post to Slack.
     *
     * @var array
     */
    public $goodTags = [
        'good-for-storytelling',
        'good-submission',
        'good-for-sponsor',
        'good-quote',
        'good-for-brand',
    ];

    /**
     * Get a post by its unique hash.
     *
     * @param string $hash
     * @return Post
     */
    public static function findByHashOrFail($hash)
    {
        $result = app(Hashids::class)->decode($hash);

        if (empty($result)) {
            throw new ModelNotFoundException();
        }

        return self::findOrFail($result[0]);
    }

    /**
     * Each post belongs to an action.
     */
    public function campaign()
    {
        return $this->belongsTo(Campaign::class);
    }

    /**
     * Get the group associated with this signup.
     */
    public function group()
    {
        return $this->belongsTo(Group::class);
    }

    /**
     * Each post belongs to a signup.
     */
    public function signup()
    {
        return $this->belongsTo(Signup::class);
    }

    /**
     * Get the user associated with this signup.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'northstar_id');
    }

    /**
     * Get the user associated with this signup.
     */
    public function referrerUser()
    {
        return $this->belongsTo(User::class, 'referrer_user_id');
    }

    /**
     * Each post has one review.
     */
    public function review()
    {
        return $this->hasOne(Review::class);
    }

    /**
     * Get the reactions associated with this post.
     */
    public function reactions()
    {
        return $this->hasMany(Reaction::class);
    }

    /**
     * Each post belongs to an action.
     */
    public function actionModel()
    {
        return $this->belongsTo(Action::class, 'action_id');
    }

    /**
     * Get the reactions associated with this post
     * for the given user ID.
     */
    public function reaction()
    {
        return $this->hasOne(Reaction::class)->where(
            'northstar_id',
            auth()->id(),
        );
    }

    /**
     * Get the tags associated with this post.
     */
    public function tags()
    {
        return $this->belongsToMany(Tag::class)->withTimestamps();
    }

    /**
     * Get the tags associated with this post.
     */
    public function tagNames()
    {
        return $this->tags->pluck('tag_name');
    }

    /**
     * Get the tags associated with this post.
     */
    public function tagSlugs()
    {
        return $this->tags->pluck('tag_slug');
    }

    /**
     * Mutator for the ISO-3166-2 'location' field.
     *
     * @return void
     */
    public function setLocationAttribute($value)
    {
        $isoCodes = new \Sokil\IsoCodes\IsoCodesFactory();
        $subDivisions = $isoCodes->getSubdivisions();

        // Check that the provided value is a valid ISO-3166-2 region
        // code before saving it to the database. If not, discard.
        if (empty($value) || is_null($subDivisions->getByCode($value))) {
            $this->attributes['location'] = null;

            return;
        }

        $this->attributes['location'] = $value;
    }

    /**
     * Get the location as a human-readable name.
     *
     * @return string
     */
    public function getLocationNameAttribute()
    {
        $code = $this->attributes['location'];
        if (!$code) {
            return null;
        }

        return Cache::rememberForever('region-' . $code, function () use (
            $code
        ) {
            $isoCodes = new \Sokil\IsoCodes\IsoCodesFactory();
            $subDivisions = $isoCodes->getSubdivisions();
            $region = $subDivisions->getByCode($code);

            return $region ? $region->getLocalName() : null;
        });
    }

    /**
     * Create a hard-to-guess "hash" ID.
     *
     * @return string
     */
    public function getHashAttribute()
    {
        return app(Hashids::class)->encode($this->id);
    }

    /**
     * Get the URL for the media for this post.
     */
    public function getMediaUrl()
    {
        if ($this->url === null) {
            return null;
        }

        return url('images/' . $this->hash);
    }

    /**
     * Get the URL for the media for this post.
     */
    public function getOriginalUrl()
    {
        if ($this->url === null) {
            return null;
        }

        return url('originals/' . $this->id);
    }

    /**
     * Return the filesystem path for this post.
     *
     * @return string|null
     */
    public function getMediaPath()
    {
        $path = parse_url($this->url, PHP_URL_PATH);

        // We store local URLs prefixed with `/storage` when using the "local" adapter.
        if (config('filesystems.default') == 'local') {
            return str_replace('/storage/', '', $path);
        }

        // For S3, we store a full AWS URL, sometimes prefixed by bucket directory.
        if (config('filesystems.default') == 's3') {
            return str_replace(
                '/' . config('filesystems.disks.s3.bucket') . '/',
                '',
                $path,
            );
        }

        return null;
    }

    /**
     * Was this post created by an automated test?
     *
     * @return bool
     */
    public function isAutomatedTest()
    {
        $testCaptions = ['Test runscope upload', 'caption_ghost_test'];
        $testWhyParticipated = ['why_participated_ghost_test'];

        return in_array($this->text, $testCaptions) ||
            in_array($this->signup->why_participated, $testWhyParticipated);
    }

    /**
     * Get the siblings associated with this post.
     */
    public function siblings()
    {
        return $this->hasMany(self::class, 'signup_id', 'signup_id')->take(5);
    }

    /**
     * Transform this post for Customer.io.
     *
     * @return array
     */
    public function toCustomerIoPayload()
    {
        // Fetch Campaign Website information via GraphQL.
        $campaignWebsite = app(GraphQL::class)->getCampaignWebsiteByCampaignId(
            $this->campaign_id,
        );

        // Fetch School information via GraphQL.
        if ($this->school_id) {
            $school = app(GraphQL::class)->getSchoolById($this->school_id);
        }

        $action = $this->actionModel;
        $signup = optional($this->signup);
        $campaign = optional($signup->campaign);
        $tags = $this->tagSlugs()->toArray();

        return array_merge(
            [
                'version' => 3, // TODO: Is this used anywhere in Customer.io?
                'id' => (string) $this->id,
                'signup_id' => $this->signup_id,
                'quantity' => (int) $this->quantity,
                'hours_spent' => $this->hours_spent
                    ? (float) $this->hours_spent
                    : null,
                'why_participated' => strip_tags($signup->why_participated),
                'campaign_id' => (string) $this->campaign_id,
                'campaign_title' => Arr::get($campaignWebsite, 'title'),
                'campaign_slug' => Arr::get($campaignWebsite, 'slug'),
                'campaign_cause' => implode(',', $campaign->cause ?: []),
                'northstar_id' => $this->northstar_id,
                'type' => $this->type,
                'action' => $this->getActionName(),
                'action_id' => $this->action_id,
                'action_type' => $action->action_type,
                'scholarship_entry' => $action->scholarship_entry,
                'civic_action' => $action->civic_action,
                'quiz' => $action->quiz,
                'online' => $action->online,
                'time_commitment' => $action->time_commitment,
                'volunteer_credit' => $action->volunteer_credit,
                'url' => $this->getMediaUrl(),
                'caption' => strip_tags($this->text), // @TODO: We should be reading from 'text' in Customer.io templates.
                'text' => strip_tags($this->text),
                'status' => $this->status,
                'remote_addr' => '0.0.0.0',
                'source' => $this->source,
                'source_details' => $this->source_details,
                'details' => $this->details,
                'referrer_user_id' => $this->referrer_user_id,
                'school_id' => $this->school_id,
                'school_name' => isset($school)
                    ? Arr::get($school, 'name')
                    : null,
                'created_at' => $this->created_at->timestamp,
                'updated_at' => $this->updated_at->timestamp,
                'deleted_at' => optional($this->deleted_at)->timestamp,
                'tags' => implode(',', $tags ?: []),
            ],
            optional($this->group)->toCustomerIoPayload() ?: [],
        );
    }

    /**
     * Get the post's action name.
     * @TODO: This function should be deleted once we delete the action column from the posts table.
     */
    public function getActionName()
    {
        return $this->actionModel ? $this->actionModel['name'] : $this->action;
    }

    /**
     * Apply the given tag to this post.
     * @param $tag Tag to tag post with
     */
    public function tag($tag)
    {
        // If a tag slug is sent in, change to the tag name.
        if (strpos($tag, '-')) {
            $tagArray = explode('-', $tag);
            $tag = implode(' ', $tagArray);
        }

        // Capitalize each word in the tag.
        $tagName = ucwords($tag);

        // Only tag if the tag doesn't exist on the post yet.
        // Otherwise, an integrity constraint violation / duplicate entry error will be thrown.
        if (!$this->tagNames()->contains($tagName)) {
            $tag = Tag::firstOrCreate(
                ['tag_name' => $tagName],
                ['tag_slug' => Str::slug($tagName, '-')],
            );

            $this->tags()->attach($tag);

            // Update timestamps on the Post when adding a tag
            $this->touch();

            info('post_tagged', ['id' => $this->id, 'tag' => $tag->tag_slug]);

            if (
                !$this->hasGoodTag() &&
                in_array($tag->tag_slug, $this->goodTags)
            ) {
                $this->notify(new PostTagged($this, $tag));
            }
        }

        $this->calculateStaffFaveTagBadges();

        return $this;
    }

    /**
     * Remove the given tag from this post.
     */
    public function untag($tagName)
    {
        $tag = Tag::where('tag_name', $tagName)->first();

        $this->tags()->detach($tag);

        // Update timestamps on the Post when removing a tag
        $this->touch();

        return $this;
    }

    /**
     * Returns posts without specific tag(s).
     */
    public function scopeWithoutTag($query, $tagSlug)
    {
        return $query->whereDoesntHave('tags', function ($query) use (
            $tagSlug
        ) {
            multipleValueQuery($query, $tagSlug, 'tag_slug');
        });
    }

    /**
     * Returns posts with specific tag(s).
     */
    public function scopeWithTag($query, $tagSlug)
    {
        return $query->whereHas('tags', function ($query) use ($tagSlug) {
            multipleValueQuery($query, $tagSlug, 'tag_slug');
        });
    }

    /**
     * Returns posts which qualify for volunteer credit.
     * (The associated Action's volunteer credit field is set to true).
     */
    public function scopeWithVolunteerCredit($query)
    {
        return $query->whereHas('actionModel', function ($query) {
            $query->where('volunteer_credit', true);
        });
    }

    /**
     * Returns posts which do not qualify for volunteer credit.
     * (The associated Action's volunteer credit field is set to false).
     */
    public function scopeWithoutVolunteerCredit($query)
    {
        return $query->whereHas('actionModel', function ($query) {
            $query->where('volunteer_credit', false);
        });
    }

    /**
     * Returns posts that are reviewable.
     */
    public function scopeWhereReviewable($query)
    {
        return $query->whereIn('type', ['photo', 'text']);
    }

    /**
     * Get the number of reactions a post has.
     *
     * @param int $postId
     * @return int
     */
    public function getTotalReactions($postId)
    {
        return Reaction::where('post_id', $postId)->count();
    }

    /**
     * Scope a query to only return posts if one of these conditions is true:
     * - authenticated user is a staffer
     * - the post status is accepted
     * - authenticated user is owner of post
     * - the post is type voter-reg, status is register-*, and authenticated user is the referrer.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWhereVisible($query)
    {
        if (is_staff_user()) {
            return;
        }

        return $query
            ->whereIn(
                'status',
                array_merge(['accepted'], self::getCompletedVoterRegStatuses()),
            )
            ->orWhere('northstar_id', auth()->id())
            ->orWhere(function ($query) {
                $query
                    ->whereNotNull('referrer_user_id')
                    ->where('referrer_user_id', auth()->id())
                    ->where('type', 'voter-reg');
            });
    }

    /**
     * Scope a query to only return anonymous posts if a user is an admin, staff, or is owner of post.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithoutAnonymousPosts($query)
    {
        if (!is_staff_user()) {
            return $query
                ->whereDoesntHave('actionModel', function ($query) {
                    $query->where('anonymous', true);
                })
                ->orWhere('northstar_id', auth()->id());
        }
    }

    /**
     * Scope a query to only return posts tagged as "Hide In Gallery" if a user is an admin, staff, or is owner of post.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWithHiddenPosts($query)
    {
        if (!is_staff_user()) {
            return $query
                ->whereDoesntHave('tags', function ($query) {
                    $query->where('tag_slug', 'hide-in-gallery');
                })
                ->orWhere('northstar_id', auth()->id());
        }
    }

    /**
     * Return whether or not a post already has a "good" tag.
     *
     * @return bool
     */
    public function hasGoodTag()
    {
        foreach ($this->tagSlugs() as $tagslug) {
            if (in_array($tagslug, $this->goodTags)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the number of posts for the given campaign and status.
     *
     * @return int
     */
    public static function getPostCount(Campaign $campaign, string $status)
    {
        return (new self())
            ->newModelQuery()
            ->where('campaign_id', $campaign->id)
            ->where('status', $status)
            ->whereReviewable()
            ->count();
    }

    /**
     * Updates or creates an ActionStat for a completed post action if it belongs to a school.
     *
     * @return void
     */
    public function updateOrCreateActionStats()
    {
        // We currently only save action stats for posts that belong to schools.
        if (!$this->school_id) {
            return;
        }

        /**
         * TODO: We can eventually query GraphQL for school location if we have a school but no
         * group -- but there aren't any current school finder campaigns that would create posts
         * with a school and not a group.
         */
        $location = $this->group ? $this->group->location : null;

        // If completed voter-reg post, update school impact as completed count for this action.
        if (
            $this->type === 'voter-reg' &&
            in_array($this->status, self::getCompletedVoterRegStatuses())
        ) {
            $impact = (new self())
                ->newModelQuery()
                ->where('school_id', $this->school_id)
                ->where('type', 'voter-reg')
                ->where('action_id', $this->action_id)
                ->whereIn('status', self::getCompletedVoterRegStatuses())
                ->count();

            return ActionStat::updateOrCreate(
                [
                    'action_id' => $this->action_id,
                    'school_id' => $this->school_id,
                ],
                ['impact' => $impact, 'location' => $location],
            );
        }

        // If approved photo post, update school impact as sum quantity for this action.
        if ($this->type === 'photo' && $this->status === 'accepted') {
            $impact = (new self())
                ->newModelQuery()
                ->where('action_id', $this->action_id)
                ->where('school_id', $this->school_id)
                ->where('status', 'accepted')
                ->sum('quantity');

            return ActionStat::updateOrCreate(
                [
                    'action_id' => $this->action_id,
                    'school_id' => $this->school_id,
                ],
                ['impact' => $impact, 'location' => $location],
            );
        }
    }

    /**
     * Gets event payload for a referral post, on behalf of the referrer user ID.
     *
     * @return array
     */
    public function getReferralPostEventPayload()
    {
        return array_merge(
            [
                'id' => $this->id,
                'user_id' => $this->northstar_id,
                'user_display_name' => $this->user->display_name,
                'type' => $this->type,
                'status' => $this->status,
                'action_id' => $this->action_id,
                'created_at' => $this->created_at->timestamp,
                'updated_at' => $this->updated_at->timestamp,
            ],
            optional($this->group)->toCustomerIoPayload() ?: [],
        );
    }

    /**
     * Returns all status values for completed voter registrations.
     *
     * @return array
     */
    public static function getCompletedVoterRegStatuses()
    {
        return ['register-form', 'register-OVR'];
    }

    /**
     * Route notifications for the Slack channel.
     *
     * @param \Illuminate\Notifications\Notification $notification
     * @return string
     */
    public function routeNotificationForSlack($notification)
    {
        return config('services.slack.url');
    }

    /**
     * Checks whether a user should be given a badge based on their post.
     */
    public function calculatePostBadges()
    {
        $user = $this->user;
        if ($user) {
            $userPostsCount = $user->posts()->count();
            if ($userPostsCount >= 4) {
                $user->addBadge(BadgeType::get('FOUR_POSTS'));
            }
            if ($userPostsCount >= 3) {
                $user->addBadge(BadgeType::get('THREE_POSTS'));
            }
            if ($userPostsCount >= 2) {
                $user->addBadge(BadgeType::get('TWO_POSTS'));
            }
            if ($userPostsCount >= 1) {
                $user->addBadge(BadgeType::get('ONE_POST'));
            }
            $user->save();
        }
    }

    /**
     * Checks whether a user should be given a badge based on if their post has been tagged
     * a good-submission by staff.
     */
    public function calculateStaffFaveTagBadges()
    {
        $user = $this->user;
        if ($user) {
            $userPostsWithFaveTagCount = $user
                ->posts()
                ->withTag('good-submission')
                ->take(3)
                ->count();

            if ($userPostsWithFaveTagCount >= 3) {
                $user->addBadge(BadgeType::get('THREE_STAFF_FAVES'));
            }
            if ($userPostsWithFaveTagCount >= 2) {
                $user->addBadge(BadgeType::get('TWO_STAFF_FAVES'));
            }
            if ($userPostsWithFaveTagCount >= 1) {
                $user->addBadge(BadgeType::get('ONE_STAFF_FAVE'));
            }

            $user->save();
        }
    }
}
