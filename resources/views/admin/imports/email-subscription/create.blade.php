@extends('admin.layouts.importer')

@section('title', 'Email Subscriptions Imports')

@section('main_content')

<h1>Email Subscriptions</h1>

<form action="/admin/imports" method="post" enctype="multipart/form-data">
    {{ csrf_field() }}

    <div class="form-group">
        <p class="lead">
        Creates or updates users and their email subscriptions per uploaded CSV.
        </p>

        <p>Columns:</p>

        <ul>
        <li><code>email</code> - required</li>
        <li><code>first_name</code> - optional</li>
        </ul>
    </div>

    <h3>Users</h3>

    <div class="form-group row">
        <label for="source-detail" class="col-sm-3 col-form-label" required>Source detail</label>

        <div class="col-sm-9">
            <input type="text" class="form-control" name="source-detail" placeholder="breakdown_opt_in" value="{{ old('source-detail') }}">

            <small class="form-text text-muted">
            Specify the <code>source_detail</code> for new users that will be created from this upload.
            </small>
        </div>
        </div>

        <div class="form-group row">
        <label for="source-detail" class="col-sm-3 col-form-label" required>Subscription topic</label>

        <div class="col-sm-9">
            @foreach ($config['topics'] as $topic => $config)
            <div class="form-check">
                <input class="form-check-input" name="topic" type="radio" value="{{ $topic }}" id="community">

                <label class="form-check-label" for="{{ $topic }}">

                {{ $topic }}

                @isset($config['reset'])
                    <small class="form-text text-muted"> - Sending <code>{{$config['reset']['type']}}</code> email is {{$config['reset']['enabled'] ? 'ON' : 'OFF'}} for new users.</small>
                @endif
                </label>
            </div>
            @endforeach

            <small class="form-text text-muted">
                Select the email subscription topics to subscribe new or existing user to.<br />
                <strong>Note</strong> - This will append (not overwrite) subscriptions for existing users.
            </small>
        </div>
    </div>

    <hr />

    @include('admin.imports.partials.upload-file')

    <div>
        <input type="submit" class="btn btn-primary btn-lg" value="Import">
    </div>
</form>

<hr />

@include('admin.imports.partials.progress')

@endsection
