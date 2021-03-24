<?php

namespace App\Http\Controllers\Web\Admin;

use App\Http\Controllers\Controller;
use App\Models\Club;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ClubsController extends Controller
{
    /**
     * Create a controller instance.
     */
    public function __construct()
    {
        $this->middleware('auth:web');
        $this->middleware('role:admin,staff');

        $this->rules = [
            'name' => 'required|string|max:255',
            'city' => 'nullable|string',
            'location' => 'nullable|iso3166',
            'school_id' => 'nullable|string|max:255',
        ];
    }

    public function show(Club $club)
    {
        // @Question: Not sure why Club model does not have relationship methods
        // for user and school? Maybe because of microservices?

        $user = User::findOrFail($club->leader_id);

        $school = Group::find($club->school_id);

        return Inertia::render('Clubs/Show', [
            'club' => $club,
            'school' => $school,
            'user' => $user->only('first_name', 'id'),
        ]);
    }

    /**
     * Create a new club.
     */
    public function create()
    {
        return view('admin.clubs.create');
    }

    /**
     * Store a newly created club in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function store(Request $request)
    {
        $this->validate(
            $request,
            array_merge_recursive($this->rules, [
                'leader_id' => 'required|objectid|unique:mysql.clubs',
            ]),
        );

        $club = Club::create($request->all());

        // Log that a club was created.
        info('club_created', ['id' => $club->id]);

        return redirect("/admin/clubs/$club->id/edit")->with(
            'flash',
            'Club successfully created!',
        );
    }

    /**
     * Edit an existing club.
     *
     * @param  \App\Models\Club  $club
     */
    public function edit(Club $club)
    {
        return view('admin.clubs.edit')->with([
            'club' => $club,
        ]);
    }

    /**
     * Update the specified club in storage.
     *
     * @param  \App\Models\Club  $club
     * @param  \Illuminate\Http\Request  $request
     */
    public function update(Club $club, Request $request)
    {
        $this->validate(
            $request,
            array_merge_recursive($this->rules, [
                'leader_id' => [
                    'required',
                    'objectid',
                    Rule::unique('mysql.clubs')->ignore($club),
                ],
            ]),
        );

        $club->update($request->all());

        // Log that a club was updated.
        info('club_updated', ['id' => $club->id]);

        return redirect()
            ->back()
            ->with('flash', 'Club successfully updated!');
    }
}
