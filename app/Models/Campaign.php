<?php

namespace App\Models;

use App\Models\Traits\HasCursor;
use App\Types\Cause;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Laravel\Scout\Searchable;

class Campaign extends Model
{
    use HasCursor, Searchable, SoftDeletes;

    /**
     * The database connection that should be used by the model.
     *
     * @var string
     */
    protected $connection = 'mysql';

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['has_website', 'is_evergreen', 'is_group_campaign'];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['start_date', 'end_date'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'internal_title',
        'cause',
        'impact_doc',
        'start_date',
        'end_date',
        'contentful_campaign_id',
        'group_type_id',
    ];

    /**
     * Attributes that can be queried when filtering.
     *
     * This array is manually maintained. It does not necessarily mean that
     * any of these are actual indexes on the database... but they should be!
     *
     * @var array
     */
    public static $indexes = ['id', 'group_type_id'];

    /**
     * Attributes that can be sorted by.
     *
     * This array is manually maintained. It does not necessarily mean that
     * any of these are actual indexes on the database... but they should be!
     *
     * @var array
     */
    public static $sortable = [
        'id',
        'accepted_count',
        'pending_count',
        'start_date',
    ];

    /**
     * Get the signups associated with this campaign.
     */
    public function signup()
    {
        return $this->hasMany(Signup::class);
    }

    /**
     * A campaign has many actions.
     */
    public function actions()
    {
        return $this->hasMany(Action::class);
    }

    /**
     * A campaign has many posts.
     */
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    /**
     * Scope a query to only include "open" campaigns.
     */
    public function scopeWhereOpen($query)
    {
        $today = now()->format('Y-m-d');

        return $query
            ->whereDate('start_date', '<=', $today)
            ->where(function (Builder $query) use ($today) {
                $query
                    ->whereNull('end_date')
                    ->orWhereDate('end_date', '>', $today);
            });
    }

    /**
     * Scope a query to only include "closed" campaigns.
     */
    public function scopeWhereClosed($query)
    {
        $today = now()->format('Y-m-d');

        return $query
            ->whereDate('start_date', '>', $today)
            ->orWhere(function (Builder $query) use ($today) {
                $query
                    ->whereNotNull('end_date')
                    ->whereDate('end_date', '<', $today);
            });
    }

    /**
     * Scope a query to only include campaigns with an associated Contentful 'Website' entry.
     */
    public function scopeWhereHasWebsite($query)
    {
        return $query->whereNotNull('contentful_campaign_id');
    }

    /**
     * Scope a query to only include campaigns without an associated Contentful 'Website' entry.
     */
    public function scopeWhereDoesNotHaveWebsite($query)
    {
        return $query->whereNull('contentful_campaign_id');
    }

    /**
     * Scope a query to only include campaigns containing specified causes.
     */
    public function scopeWithCauses($query, $causes)
    {
        // Sanitize the inputted causes against our internal cause list.
        $inputCauses = explode(',', $causes);
        $sanitizedInputCauses = array_intersect(Cause::all(), $inputCauses);

        // Merge cause list separated by the regex "or" operator to filter by multiple causes.
        $causesRegex = implode('|', $sanitizedInputCauses);

        // Regex matches on comma separated words to ensure precise match for each cause.
        // Accounts for first and last words only having a comma on one side.
        // (https://regex101.com/r/RaDXos/6).
        return $query->where(
            'cause',
            'regexp',
            '(^|,)' . $causesRegex . '(,|$)',
        );
    }

    /**
     * Should we accept new signups & posts for this campaign?
     *
     * @return bool
     */
    public function isOpen()
    {
        $hasStarted = $this->start_date < now();
        $hasEnded = $this->end_date && $this->end_date < now();

        return $hasStarted && !$hasEnded;
    }

    /**
     * Get a human-friendly list of this campaign's causes.
     *
     * @return array
     */
    public function getCauseNames()
    {
        return array_values(
            array_intersect_key(Cause::labels(), array_flip($this->cause)),
        );
    }

    /**
     * Accessor for parsing comma-separated causes into an array.
     *
     * @return array
     */
    public function getCauseAttribute()
    {
        $cause = $this->attributes['cause'];

        return blank($cause) ? [] : explode(',', $cause);
    }

    /**
     * Mutator for storing an array of causes as a comma-separated string.
     *
     * @param string|Carbon $value
     */
    public function setCauseAttribute($value)
    {
        $this->attributes['cause'] = implode(',', $value);
    }

    /**
     * Accessor for getting the start_date field.
     */
    public function getStartDateAttribute()
    {
        $value = $this->attributes['start_date'];
        if (!$value) {
            return null;
        }
        //explicitly setting our timezone to EST to account for accurate end dates displayed on Phoenix
        return convert_to_date($value, 'America/New_York');
    }

    /**
     * Mutator for setting the start_date field.
     *
     * @param string|Carbon $value
     */
    public function setStartDateAttribute($value)
    {
        $this->setArbitraryDateString('start_date', $value);
    }

    /**
     * Accessor for getting the end_date field.
     */
    public function getEndDateAttribute()
    {
        $value = $this->attributes['end_date'];
        if (!$value) {
            return null;
        }
        //explicitly setting our timezone to EST to account for accurate end dates displayed on Phoenix
        return convert_to_date($value, 'America/New_York');
    }

    /**
     * Mutator for setting the end_date field.
     *
     * @param string|Carbon $value
     */
    public function setEndDateAttribute($value)
    {
        $this->setArbitraryDateString('end_date', $value);
    }

    /**
     * Accessor for determining if this campaign has an associated website.
     *
     * @return bool
     */
    public function getHasWebsiteAttribute()
    {
        // If we have an assigned contentful campaign ID, there should be an associated website.
        return isset($this->attributes['contentful_campaign_id']);
    }

    /**
     * Accessor for determining if this campaign is evergreen.
     *
     * @return bool
     */
    public function getIsEvergreenAttribute()
    {
        // If we don't have an assigned end date, this campaign is evergreen.
        return is_null($this->attributes['end_date']);
    }

    /**
     * Accessor for determining if this campaign is associated with a specific Group Type.
     *
     * @return bool
     */
    public function getIsGroupCampaignAttribute()
    {
        return isset($this->attributes['group_type_id']);
    }

    /**
     * Gets campaign by action_id.
     *
     * @param $actionId
     */
    public static function fromActionId($actionId)
    {
        $action = Action::findOrFail($actionId);

        return self::findOrFail($action->campaign_id);
    }

    /**
     * Mutator to parse non-standard date strings into dates accepted by Laravel.
     *
     * @param string|Carbon $value
     */
    public function setArbitraryDateString($attribute, $value)
    {
        if (is_null($value)) {
            $this->attributes[$attribute] = null;

            return;
        }

        // Parse user-entered strings like '10/31/1990' or `October 31st 1990'.
        if (is_string($value)) {
            $value = strtotime($value);
        }

        $this->attributes[$attribute] = $this->fromDateTime($value);
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        $array = $this->toArray();

        // Only index the first 20 actions to prevent exceeding the permitted Algolia entry size (10kb).
        // (A select few campaigns contain an overwhelming amount of actions. https://bit.ly/38WOlgq).
        $actions = $this->actions->slice(0, 20);

        // Append data from Action model relationship.
        $array['actions'] = $actions->map(function ($data) {
            return Arr::only($data->toArray(), [
                'action_type',
                'anonymous',
                'civic_action',
                'name',
                'noun',
                'online',
                'post_type',
                'quiz',
                'reportback',
                'scholarship_entry',
                'time_commitment',
                'verb',
                'volunteer_credit',
            ]);
        });

        // Transform dates into UNIX timestamps:
        foreach ($this->dates as $date) {
            $array[$date] = optional($this->{$date})->timestamp;
        }

        // Only send data we want to search against.
        return Arr::only($array, [
            'accepted_count',
            'actions',
            'contentful_campaign_id',
            'cause',
            'end_date',
            'has_website',
            'id',
            'internal_title',
            'is_evergreen',
            'is_group_campaign',
            'pending_count',
            'secondary_causes',
            'start_date',
        ]);
    }
}
