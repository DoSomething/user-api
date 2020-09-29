@extends('layouts.app')

@section('title', $title. ' | DoSomething.org')

@section('content')
    <div class="container -padded">
        <div class="wrapper">
            <div class="container__block -centered">
                <h1>{{ $header }}</h1>
                <h3>{{ $instructions }}</h3>
            </div>
            <div class="container__block -centered">
                @if (count($errors) > 0)
                    <div class="validation-error fade-in-up">
                        <h4>{{ trans('auth.validation.issues') }}</h4>
                        <ul class="list -compacted">
                            @foreach ($errors->all() as $error)
                                <li>
                                    {{ $error }} 
                                    @if (strpos($error, 'create-password'))
                                        <a href="/password/reset">Resent email to create/reset password</a>
                                    @endif
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif              
                <form id="password-reset-form" role="form" method="POST" action="{{ url('/password/reset/'.$type) }}">
                    {{ csrf_field() }}

                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="form-item">
                        <label for="email" class="field-label">{{ trans('auth.fields.email') }}</label>
                        <input name="email" readonly type="text" class="text-field is-disabled" placeholder="puppet-sloth@example.org" value="{{ $email }}">
                    </div>

                    <div class="form-item">
                        <label for="password" class="field-label">{{ $new_password_field }}</label>
                        <input name="password" type="password" class="text-field" placeholder="••••••">
                    </div>

                    <div class="form-item">
                        <label for="password_confirmation" class="field-label">{{ $confirm_new_password_field }}</label>
                        <input name="password_confirmation" type="password" class="text-field" placeholder="••••••">
                    </div>

                    <div class="form-actions -padded">
                        <input id="reset-password" type="submit" class="button" value="{{ $new_password_submit }}">
                    </div>
                </form>
            </div>
            @if ($display_footer)
                <div class="container__block -centered">
                    <ul>
                        <li><a href="{{ url('login') }}" class="login-link">{{ trans('auth.log_in.existing') }}</a></li>
                        <li><a href="{{ url('register') }}" class="register-link">{{ trans('auth.log_in.create') }}</a></li>
                    </ul>
                </div>
            @endif
        </div>
    </div>
@stop
