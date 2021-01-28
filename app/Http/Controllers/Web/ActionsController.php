<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Action;
use App\Types\ActionType;
use App\Types\PostType;
use App\Types\TimeCommitment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ActionsController extends Controller
{
    /**
     * Create a controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin,staff');

        $this->rules = [
            'name' => ['required', 'string'],
            'post_type' => ['required', 'string', Rule::in(PostType::all())],
            'action_type' => [
                'required',
                'string',
                Rule::in(ActionType::all()),
            ],
            'time_commitment' => [
                'required',
                'string',
                Rule::in(TimeCommitment::all()),
            ],
            'callpower_campaign_id' => [
                'nullable',
                'required_if:post_type,phone-call',
                'integer',
            ],
            'noun' => ['required', 'string'],
            'verb' => ['required', 'string'],
        ];
    }

    /**
     * Create a new action.
     */
    public function create($campaignId)
    {
        return view('actions.create')->with([
            'postTypes' => PostType::labels(),
            'actionTypes' => ActionType::labels(),
            'timeCommitments' => TimeCommitment::labels(),
            'campaignId' => (int) $campaignId,
            'isAdminUser' => is_admin_user(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function store(Request $request)
    {
        $request = $this->fillInOmittedCheckboxes($request);

        $this->validate(
            $request,
            array_merge_recursive($this->rules, [
                'campaign_id' => ['required', 'integer', 'exists:campaigns,id'],
                'callpower_campaign_id' => [Rule::unique('actions')],
            ]),
        );

        // Check to see if the action exists before creating one.
        // @TODO: Remove once we're no longer fetching by this combination of fields.
        $action = Action::where([
            'name' => $request['name'],
            'campaign_id' => $request['campaign_id'],
            'post_type' => $request['post_type'],
        ])->first();

        if (!$action) {
            $action = Action::create($request->all());

            // Log that a action was created.
            info('action_created', ['id' => $action->id]);
        }

        return redirect('actions/' . $action->id);
    }

    /**
     * Edit an existing action.
     *
     * @param  \App\Models\Action  $action
     * @param  $campaignId
     */
    public function edit(Action $action)
    {
        return view('actions.edit')->with([
            'action' => $action,
            'postTypes' => PostType::labels(),
            'actionTypes' => ActionType::labels(),
            'timeCommitments' => TimeCommitment::labels(),
            'isAdminUser' => is_admin_user(),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Models\Action  $action
     * @param  \Illuminate\Http\Request  $request
     */
    public function update(Action $action, Request $request)
    {
        $request = $this->fillInOmittedCheckboxes($request);

        $this->validate(
            $request,
            array_merge_recursive($this->rules, [
                'callpower_campaign_id' => [
                    Rule::unique('actions')->ignore($action->id),
                ],
            ]),
        );

        $action->update($request->all());

        // Log that an action was updated.
        info('action_updated', ['id' => $action->id]);

        return redirect('actions/' . $action->id);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Action  $action
     */
    public function destroy(Action $action)
    {
        $action->forceDelete();

        // Log that an action was deleted.
        info('action_deleted', ['id' => $action->id]);

        return $this->respond('Action deleted.', 200);
    }

    /**
     * Fill in any omitted boolean values.
     *
     * @param \Illuminate\Http\Request
     * @return \Illuminate\Http\Request
     */
    public function fillInOmittedCheckboxes(Request $request)
    {
        // Frustratingly, browsers will just omit an unchecked field from the
        // request. To ensure we can "unset" checked fields, we'll update the
        // request so any boolean fields are set 'false' if omitted.
        foreach (Action::getBooleans() as $field) {
            $request[$field] = $request->has($field);
        }

        return $request;
    }
}
