@extends('layouts.master')

@section('main_content')

    @include('layouts.header', ['header' => 'New Group'])

    <div class="container -padded">
        <div class="wrapper">
            <div class="container__block -narrow">
                <h1>
                    <a href="/group-types/{{ $groupTypeId }}">Group Type #{{ $groupTypeId }}</a>
                </h1>
                <form method="POST" action="{{ route('groups.store', ['group_type_id' => $groupTypeId]) }}">
                    {{ csrf_field()}}

                    <div class="form-item">
                        <label class="field-label">Name</label>
                        @include('forms.text', ['name' => 'name', 'placeholder' => 'Group name, e.g. NYC Chapter'])
                    </div>

                    <div class="form-item">
                        <label class="field-label">Goal</label>
                        @include('forms.text', ['name' => 'goal', 'placeholder' => 'Optional group goal, e.g. 200'])
                    </div>

                    <div class="form-item">
                        <label class="field-label">City</label>
                        @include('forms.text', ['name' => 'city', 'placeholder' => ' e.g. San Antonio'])
                    </div>

                    <div class="form-item">
                        <label class="field-label">Location</label>
                        @include('forms.text', ['name' => 'location', 'placeholder' => ' e.g. US-TX'])
                    </div>

                    <div class="form-item">
                        <label class="field-label">School ID</label>
                        @include('forms.text', ['name' => 'school_id', 'placeholder' => 'The school universal ID associated with this group'])
                    </div>

                    <ul class="form-actions -inline -padded">
                        <li><input type="submit" class="button" value="Create Group"></li>
                    </ul>
                </form>
            </div>
        </div>
    </div>

@stop
