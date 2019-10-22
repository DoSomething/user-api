@extends('layouts.app')

@section('title', 'Log In | DoSomething.org')

@section('content')
    @if (session('request_reset'))
        <div class="messages -padded">You need to <a href="{{ url('password/reset') }}">reset your password</a> before you can log in.</div>
    @endif

    <div class="container__block -centered">
        <h2 class="heading -alpha">{{ trans('auth.greeting.lets_do_this') }}</h2>
        <h3>{{ trans('auth.greeting.continue') }} {{ session('destination', 'DoSomething.org') }}.</h3>
    </div>

    <div class="container__block -centered">
        @if (count($errors) > 0)
            <div class="validation-error fade-in-up">
                <h4>{{ trans('auth.validation.issues') }}</h4>
                <ul class="list -compacted">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form id="profile-login-form" method="POST" action="{{ url('login') }}">
            <input name="_token" type="hidden" value="{{ csrf_token() }}">

            <div class="form-item">
                <label for="username" class="field-label">
                    <div class="validation">
                        <div class="validation__label">{{ trans('auth.fields.email_or_mobile') }} <span class="form-required" title="This field is required.">*</span></div>
                        <div class="validation__message"></div>
                    </div>
                </label>
                <input name="username" type="text" class="text-field required" placeholder="puppet-sloth@example.org" value="{{ old('username') }}" autofocus />
            </div>

            <div class="form-item password-visibility">
                <label for="password" class="field-label">
                    <div class="validation">
                        <div class="validation__label">{{ trans('auth.fields.password') }} <span class="form-required" title="This field is required.">*</span></div>
                        <div class="validation__message"></div>
                    </div>
                </label>
                <input name="password" type="password" class="text-field required" placeholder="••••••••" />
                <span class="password-visibility__toggle -hide"></span>
            </div>

            <div class="form-actions -padded">
                <input type="submit" id="login-submit" class="button" value="{{ trans('auth.log_in.default') }}">
            </div>
        </form>
    </div>

    <div class="container__block -centered">
        <div class="w-full">
            @include('auth.facebook')
        </div>
        <div class="flex justify-center">
            <div class="w-full md:w-3/4">
                @include('auth.google')
            </div>
        </div>

        <ul>
            <li><a href="{{ url('register') }}" class="register-link">{{ trans('auth.log_in.create') }}</a></li>
            <li><a href="{{ url('password/reset') }}" class="forgot-password-link">{{ trans('auth.forgot_password.header') }}</a></li>
        </ul>
    </div>
@stop
