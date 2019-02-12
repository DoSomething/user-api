@extends('layouts.app')

@section('title', 'Forgot Password | DoSomething.org')

@section('content')
    <div class="container -padded">
        <div class="wrapper">
            <div class="container__block -centered">
                <h1>{{ trans('auth.forgot_password.header') }}</h1>
                <h3>{{ trans('auth.forgot_password.instructions') }}</h3>
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

                <form id="forgot-password-form" role="form" method="POST" action="{{ url('/password/email') }}">
                    {{ csrf_field() }}

                    <div class="form-item">
                        <label for="email" class="field-label">{{ trans('auth.fields.email') }}</label>
                        <input name="email" type="text" class="text-field" placeholder="puppet-sloth@example.org" value="{{ $email or old('email') }}">
                    </div>

                    <div class="form-actions -padded">
                        <input id="request-reset" type="submit" class="button" value="{{ trans('auth.forgot_password.request') }}">
                    </div>
                </form>
            </div>
            <div class="container__block -centered">
                <ul>
                    <li><a href="{{ url('login') }}" class="login-link">{{ trans('auth.log_in.existing') }}</a></li>
                    <li><a href="{{ url('register') }}" class="register-link">{{ trans('auth.log_in.create') }}</a></li>
                </ul>
            </div>
        </div>
    </div>
@stop
