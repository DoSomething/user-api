@extends('layouts.master')

@section('main_content')

    @include('layouts.header', ['header' => 'New Club'])

    <div class="container -padded">
        <div class="wrapper">
            <div class="container__block -narrow">
                <form method="POST" action="{{ route('clubs.store') }}">
                    {{ csrf_field()}}

                    <div class="form-item">
                        <label class="field-label">Name</label>
                        @include('forms.text', ['name' => 'name', 'placeholder' => 'Club name, e.g. DS Staffers DoSomething Club'])
                    </div>

                    <div class="form-item">
                        <label class="field-label">Leader ID</label>
                        @include('forms.text', ['name' => 'leader_id', 'placeholder' => 'The Club Leader\'s Northstar ID'])
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
                        @include('forms.text', ['name' => 'school_id', 'placeholder' => 'The school universal ID associated with this club'])
                    </div>

                    <ul class="form-actions -inline -padded">
                        <li><input type="submit" class="button" value="Create Club"></li>
                    </ul>
                </form>
            </div>
        </div>
    </div>

@stop
