@extends('layouts.app')

@section('title', 'Profile | DoSomething.org')

@section('content')
    <div class="container__block -centered">
        <figure class="figure -medium">
            <div class="figure__media">
                <img class="avatar" alt="avatar" src="{{ $user->avatar or asset('avatar-placeholder.png') }}" />
            </div>
            <div class="figure__body">
                You are logged in as <strong>{{ $user->displayName() }}</strong>.
            </div>
        </figure>
    </div>

    <div class="container__block">
        <div class="key-value">
            {{-- @TODO: Might want to handle null values a little better so empty <dd>'s and <p>'s don't output  --}}
            <dt>First Name:</dt>
            <dd>{{ $user->first_name }}</dd>
            <dt>Last Name:</dt>
            <dd>{{ $user->last_name }}</dd>
            <dt>Email:</dt>
            <dd>{{ $user->email }}</dd>
            <dt>Mobile:</dt>
            <dd>{{ $user->mobile }}</dd>
            <dt>Birthday:</dt>
            <dd>{{ format_date($user->birthdate) }}</dd>
            <dt>Address:</dt>
            <dd>
                <p>{{ $user->addr_street1 }}</p>
                <p>{{ $user->addr_street2 }}</p>
                <p>{{ $user->addr_city }}</p>
                <p>{{ $user->addr_state }}</p>
                <p>{{ $user->addr_zip }}</p>
            </dd>
            <dt>Country:</dt>
            <dd>{{ $user->country }}</dd>
        </div>

        <div class="form-actions">
            <a href="{{ route('users.edit', $user->id) }}" class="button -secondary">Edit Profile</a>
        </div>
        <div class="form-actions">
            <a href="/password/change" class="button -secondary">Change Password</a>
        </div>
    </div>
@stop
