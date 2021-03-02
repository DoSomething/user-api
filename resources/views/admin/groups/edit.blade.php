@extends('admin.layouts.main')

@section('header_content')
    @include('admin.layouts.header', ['title' => 'Edit Group'])
@endsection

@section('main_content')
    <div class="container -padded">
        <div class="wrapper">
            <div class="container__block -narrow">
                <form method="POST" action="{{ route('admin.groups.update', $group->id) }}">
                    {{ csrf_field()}}
                    {{ method_field('PATCH') }}

                    <div class="form-item">
                        <label class="field-label">Name</label>
                        @include('admin.forms.text', ['name' => 'name', 'placeholder' => 'Group name, e.g. NYC Chapter', 'value' => $group->name])
                    </div>

                    <div class="form-item">
                        <label class="field-label">Goal</label>
                        @include('admin.forms.text', ['name' => 'goal', 'placeholder' => 'Optional group goal, e.g. 200', 'value' => $group->goal])
                    </div>

                    <div class="form-item">
                        <label class="field-label">City</label>
                        @include('admin.forms.text', ['name' => 'city', 'placeholder' => ' e.g. San Antonio', 'value' => $group->city])
                    </div>

                    <div class="form-item">
                        <label class="field-label">Location</label>
                        @include('admin.forms.text', ['name' => 'location', 'placeholder' => ' e.g. US-TX', 'value' => $group->location])
                    </div>

                    <div class="form-item">
                        <label class="field-label">School ID</label>
                        @include('admin.forms.text', ['name' => 'school_id', 'placeholder' => 'The school universal ID associated with this group', 'value' => $group->school_id])
                    </div>

                    <ul class="form-actions -inline -padded">
                        <li><input type="submit" class="button" value="Update"></li>
                        <li><a href="{{ url()->previous() }}" class="button -tertiary">Cancel</a></li>
                    </ul>
                </form>
            </div>
        </div>
    </div>
@stop
