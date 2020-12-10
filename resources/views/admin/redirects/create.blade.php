@extends('layouts.admin')

@section('title', 'Redirects')
@section('subtitle', 'Create & manage URL redirects.')

@section('main_content')
    <div class="container -padded">
        <div class="wrapper">
            <div class="container__block -narrow">
                <h1>Create Redirect</h1>
                <p>Any visitors to the address in "incoming path" will be instantly redirected to the target URL.</p>

                @include('forms.errors')

                <form action="{{ route('redirects.store') }}" method="POST">
                    {{ csrf_field() }}

                    <div class="form-item -padded">
                        <label for="path" class="field-label">Incoming Path</label>
                        <input class="text-field" placeholder="e.g. /us/campaigns/old-path" name="path" type="text" id="path">
                    </div>

                    <div class="form-item -padded">
                        <label for="target" class="field-label">Target URL</label>
                        <input class="text-field" placeholder="e.g. https://www.dosomething.org/new-path" name="target" type="text" id="target">
                    </div>

                    <input class="button" type="submit" value="Create Redirect">
                </form>
            </div>
        </div>
    </div>
@stop
