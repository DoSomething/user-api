@extends('layouts.app')

@section('title', 'Login Status | DoSomething.org')

@section('content')
    @guest
        <div class="container__block -centered">
            <figure class="figure -medium">
                <div class="figure__body footnote">
                    You are do not have a single sign-on session.
                </div>
            </figure>
        </div>

        <div class="form-actions">
            <a href="{{ url('/login') }}" class="button -secondary">Log In</a>
        </div>
    @endguest


    @auth
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

        <div class="form-actions">
            <a href="{{ url('/logout') }}" class="button -secondary">Log Out</a>
        </div>
    @endauth
@stop
