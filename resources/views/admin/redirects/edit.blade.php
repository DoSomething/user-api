@extends('layouts.admin')

@section('title', 'Redirects')
@section('subtitle', 'Create & manage URL redirects.')

@section('main_content')
    <div class="container -padded">
        <div class="wrapper">
            <div class="container__block -narrow">
                <h1><code>{{ $redirect->path }}</code></h1>

                @include('forms.errors')

                <form action="{{ route('admin.redirects.update', $redirect->id) }}" method="POST">
                    {{ method_field('PUT') }}
                    {{ csrf_field() }}

                    <div class="form-item -padded">
                        <label for="path" class="field-label">Incoming Path</label>
                        <input class="text-field" value="{{ $redirect->path }}" placeholder="e.g. /us/campaigns/old-path" name="path" type="text" id="path" disabled>
                    </div>

                    <div class="form-item -padded">
                        <label for="target" class="field-label">Target URL</label>
                        <input class="text-field" value="{{ $redirect->path }}" placeholder="e.g. https://www.dosomething.org/new-path" name="target" type="text" id="target">
                    </div>

                    <input class="button" type="submit" value="Update Redirect">
                </form>
            </div>
        </div>
    </div>
@stop
