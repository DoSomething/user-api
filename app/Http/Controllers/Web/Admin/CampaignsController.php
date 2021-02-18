<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use App\Models\GroupType;
use App\Types\Cause;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CampaignsController extends Controller
{
    /**
     * Create a controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth:web');
        $this->middleware('role:admin,staff');

        $this->rules = [
            'internal_title' => ['required', 'string'],
            'contentful_campaign_id' => ['nullable', 'string', 'max:255'],
            'cause' => ['required', 'array', 'between:1,5'],
            'cause.*' => ['string', Rule::in(Cause::all())],
            'impact_doc' => ['required', 'url'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'group_type_id' => ['nullable', 'integer'],
        ];
    }

    /**
     * Create a new campaign.
     */
    public function create()
    {
        return view('admin.campaigns.create')
            ->with('causes', Cause::labels())
            ->with('group_types', GroupType::labels());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function store(Request $request)
    {
        $values = $this->validate(
            $request,
            array_merge_recursive($this->rules, [
                'internal_title' => [Rule::unique('campaigns')],
            ]),
        );

        $campaign = Campaign::create($values);

        // Log that a campaign was created.
        info('campaign_created', ['id' => $campaign->id]);

        return $this->redirect($campaign->id);
    }

    /**
     * Edit a specific campaign page.
     *
     * @param  \App\Models\Campaign  $campaign
     */
    public function edit(Campaign $campaign)
    {
        return view('admin.campaigns.edit', [
            'campaign' => $campaign,
            'causes' => Cause::labels(),
            'group_types' => GroupType::labels(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Campaign  $campaign
     */
    public function update(Request $request, Campaign $campaign)
    {
        $values = $this->validate(
            $request,
            array_merge_recursive($this->rules, [
                'internal_title' => [
                    Rule::unique('campaigns')->ignore($campaign->id),
                ],
            ]),
        );

        $campaign->update($values);

        // Log that a campaign was updated.
        info('campaign_updated', ['id' => $campaign->id]);

        return $this->redirect($campaign->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Campaign  $campaign
     */
    public function destroy(Campaign $campaign)
    {
        $campaign->delete();

        // Log that a campaign was deleted.
        info('campaign_deleted', ['id' => $campaign->id]);

        // @TODO: redirect to campaign deleted page
    }

    /**
     * Handle redirects for old routes.
     *
     * @param string  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function redirect($id = '')
    {
        // We can't use Laravel's built-in Route::redirect here
        // since it doesn't support redirecting with parameters:
        return redirect('/admin/campaigns/' . $id);
    }
}
