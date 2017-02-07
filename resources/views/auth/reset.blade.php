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

                <form role="form" method="POST" action="{{ url('/password/reset') }}">
                    {{ csrf_field() }}

                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="form-item">
                        <label for="email" class="field-label">{{ trans('auth.fields.email') }}</label>
                        <input name="email" type="text" class="text-field" placeholder="puppet-sloth@example.org" value="{{ $email or old('email') }}">
                    </div>

                    <div class="form-item">
                        <label for="password" class="field-label">{{ trans('auth.fields.new_password') }}</label>
                        <input name="password" type="password" class="text-field" placeholder="••••••">
                    </div>

                    <div class="form-item">
                        <label for="password_confirmation" class="field-label">{{ trans('auth.fields.confirm_new_password') }}</label>
                        <input name="password_confirmation" type="password" class="text-field" placeholder="••••••">
                    </div>

                    <div class="form-actions -padded">
                        <input type="submit" class="button" value="Reset Password">
                    </div>
                </form>
            </div>
            <div class="container__block -centered">
                <ul>
                    <li><a href="{{ url('login') }}">{{ trans('auth.log_in.existing') }}</a></li>
                    <li><a href="{{ url('register') }}">{{ trans('auth.log_in.create') }}</a></li>
                </ul>
            </div>
        </div>
    </div>
@stop
