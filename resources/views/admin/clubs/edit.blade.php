@extends('layouts.master')

@section('main_content')

    @include('layouts.header', ['header' => 'Edit Club'])

    <div class="container -padded">
        <div class="wrapper">
            <div class="container__block -narrow">
                <form method="POST" action="{{ route('clubs.update', $club->id) }}">
                    {{ csrf_field()}}
                    {{ method_field('PATCH') }}

                    <div class="form-item">
                        <label class="field-label">Name</label>
                        @include('forms.text', ['name' => 'name', 'placeholder' => 'Club name, e.g. DS Staffers DoSomething Club', 'value' => $club->name])
                    </div>

                    <div class="form-item">
                        <label class="field-label">Leader ID</label>
                        @include('forms.text', ['name' => 'leader_id', 'placeholder' => 'The Club Leader\'s Northstar ID', 'value' => $club->leader_id])
                    </div>

                    <div class="form-item">
                        <label class="field-label">City</label>
                        @include('forms.text', ['name' => 'city', 'placeholder' => ' e.g. San Antonio', 'value' => $club->city])
                    </div>

                    <div class="form-item">
                        <label class="field-label">Location</label>
                        @include('forms.text', ['name' => 'location', 'placeholder' => ' e.g. US-TX', 'value' => $club->location])
                    </div>

                    <div class="form-item">
                        <label class="field-label">School ID</label>
                        @include('forms.text', ['name' => 'school_id', 'placeholder' => 'The school universal ID associated with this club', 'value' => $club->school_id])
                    </div>


                    <ul class="form-actions -inline -padded">
                        <li><input type="submit" class="button" value="Update Club"></li>
                    </ul>
                </form>
            </div>
        </div>
    </div>
@stop
