<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\GroupType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class GroupTypesController extends Controller
{
    /**
     * Create a controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin');

        $this->rules = [
            'name' => 'required',
        ];
    }

    /**
     * Create a new group type.
     */
    public function create()
    {
        return view('group-types.create');
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
                'name' => [Rule::unique('group_types')],
            ]),
        );
        // @see ActionsController->fillInOmittedCheckboxes
        $values['filter_by_location'] = $request->has('filter_by_location');

        $groupType = GroupType::create($values);

        // Log that a group type was created.
        info('group_type', ['id' => $groupType->id]);

        return redirect('group-types/' . $groupType->id);
    }

    /**
     * Edit an existing action.
     *
     * @param  \App\Models\GroupType  $groupType
     */
    public function edit(GroupType $groupType)
    {
        return view('group-types.edit')->with([
            'groupType' => $groupType,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Models\GroupType  $groupType
     * @param  \Illuminate\Http\Request  $request
     */
    public function update(GroupType $groupType, Request $request)
    {
        $values = $this->validate(
            $request,
            array_merge_recursive($this->rules, [
                'name' => [Rule::unique('group_types')->ignore($groupType->id)],
            ]),
        );
        // @see ActionsController->fillInOmittedCheckboxes
        $values['filter_by_location'] = $request->has('filter_by_location');

        $groupType->update($values);

        // Log that a group type was updated.
        info('group_type_updated', ['id' => $groupType->id]);

        return redirect('group-types/' . $groupType->id);
    }
}
