@extends('layouts.admin')

@section('title', 'Users')
@section('subtitle', 'View & edit member profiles.')

@section('main_content')
    <div class="container">
        <div class="wrapper">
            @if ($user->deletion_requested_at)
                <div class="container__block">
                    <div class="danger-zone">
                        <strong>This user requested to delete their account on {{ (new Carbon\Carbon($user->deletion_requested_at))->format('F d, Y')}}.</strong>
                        We automatically process account deletions two weeks after the request is made. Users can "undo" this request from their account page.
                    </div>
                </div>
            @endif

            <div class="container__block profile-settings">
                <h1>{{ $user->display_name }}</h1>
                @include('forms.errors')
                <dt>Source:</dt>
                <dd>
                    {{ $user->source ?? '—' }}
                    <span class="footnote">({{ $user->source_detail ?? 'N/A' }})</span>
                </dd>
                <dt>Referrer:</dt>
                <dd>
                    @if ($user->referrer_user_id)
                        <a href="{{ route('admin.users.show', ['user' => $user->referrer_user_id]) }}">{{$user->referrer_user_id}}</a>
                    @else
                    -
                    @endif
                </dd>
                <dt>Feature Flags:</dt><dd><code>{{ isset($user->feature_flags) ? json_encode($user->feature_flags) :  '—'}}</code></dd>
                @include('admin.partials.field', ['field' => 'role'])
            </div>
            <div class="container__block -half profile-settings">
                @include('admin.users.partials.profile')
                <div class="container profile-section">
                    <h3>Subscriptions</h3>
                    @include('admin.users.partials.subscriptions')
                </div>
                <div class="container -padded">
                    <h3>Interests</h3>
                    <dt>Causes:</dt><dd>{{ $user->causes ? implode(",  ",$user->causes) : '—'}}</dd>
                </div>
            </div>

            <div class="container__block -half">
                <div class="container -padded">
                    @if(Auth::user()->hasRole('admin'))
                      @include('admin.users.partials.danger-zone', ['passwordResetTypes' => $passwordResetTypes])
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="wrapper">
            <div class="container__block -half">
                @if(Auth::user()->hasRole('admin'))
                    <a class="primary" href="{{ route('admin.users.edit', ['user' => $user->id]) }}">Edit user's profile</a>
                @endif
                <p class="footnote">
                    <strong>Updated:</strong> {{ $user->updated_at->format('F d, Y g:ia') }} ({{ $user->created_at->diffForHumans() }})<br />
                    <strong>Created:</strong> {{ $user->created_at->format('F d, Y g:ia') }} ({{ $user->created_at->diffForHumans() }})<br />
                </p>
            </div>
        </div>
    </div>

    <div class="container -padded">
        <div class="wrapper">
            <div class="container__block -narrow profile-settings">
                <h3>Links</h3>
                <ul>
                    <li>
                        <a href="{{ config('services.customerio.profile_url') }}/{{ $user->id }}">Customer.io</a>
                    </li>
                    <li>
                        <a href="{{ config('services.gambit.profile_url') }}/{{ $user->id }}">Gambit</a>
                    </li>
                    <li>
                        <a href="{{ config('services.rogue.url') }}/users/{{ $user->id }}">Rogue</a>
                    </li>
            </div>
        </div>
    </div>
@stop
