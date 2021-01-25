@extends('layouts.master')

@section('main_content')

    @include('layouts.header', ['header' => 'Rogue', 'subtitle' => 'Please log in with your DoSomething.org admin account to continue.'])

    <div class="container -padded">
        <div class="wrapper">
            <div class="container__block -narrow">
                <p>
                    Welcome to <strong>Rogue</strong>, our one source of truth for all things user activity. Logging in will give you access to member submissions and campaign/action IDs.
                </p>
                <br>
                <a href="/login" class="button">Log In</a>
                <br>
                <div class="container__block">
                    <p>Unauthorized? Please contact #help-product in Slack for admin access.</p>
                </div>
            </div>
        </div>
    </div>

@stop
