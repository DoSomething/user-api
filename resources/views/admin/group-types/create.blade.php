@extends('layouts.master')

@section('main_content')

    @include('layouts.header', ['header' => 'New Group Type'])

    <div class="container -padded">
        <div class="wrapper">
            <div class="container__block -narrow">
                <form method="POST" action="{{ route('group-types.store') }}">
                    {{ csrf_field()}}

                    <div class="form-item">
                        <label class="field-label">Name</label>
                        @include('forms.text', ['name' => 'name', 'placeholder' => 'e.g. March For Our Lives, DoSomething Clubs'])
                    </div>

                    <div class="form-item">
                        <label class="field-label">Group Finder</label>
                        @include('forms.option', ['name' => 'filter_by_location', 'label' => 'Filter by location'])
                    </div>

                    <ul class="form-actions -inline -padded">
                        <li><input type="submit" class="button" value="Create"></li>
                    </ul>
                </form>
            </div>
        </div>
    </div>
@stop
