@extends('layouts.app')

@section('title', 'Change Password | DoSomething.org')

@section('content')
    <div class="container -padded">
        <div class="container__block -centered">
          <h2 class="heading -alpha">Change password</h2>
        </div>
        <div class="wrapper">
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

                <form id="change-password-form" role="form" method="POST" action="{{ url('/password/change') }}">
                    {{ csrf_field() }}

                    <div class="form-item">
                        <label for="current_password" class="field-label">{{ trans('auth.fields.current_password') }}</label>
                        <input name="current_password" type="password" class="text-field" placeholder="••••••">
                    </div>

                    <div class="form-item">
                        <label for="new_password" class="field-label">{{ trans('auth.fields.new_password') }}</label>
                        <input name="new_password" type="password" class="text-field" placeholder="••••••">
                    </div>

                    <div class="form-item">
                        <label for="new_password_confirmation" class="field-label">{{ trans('auth.fields.confirm_new_password') }}</label>
                        <input name="new_password_confirmation" type="password" class="text-field" placeholder="••••••">
                    </div>

                    <div class="form-actions -padded">
                        <input id="reset-password" type="submit" class="button" value="{{ trans('auth.change_password.submit_change_password') }}">
                    </div>
                </form>
            </div>
            <div class="container__block -centered">
                <div class="form-actions">
                    <a href="{{ url('password/reset') }}">{{ trans('auth.forgot_password.header') }}</a>
                </div>
                <div class="form-actions">
                    <a href="/">Cancel</a>
                </ul>
            </div>
        </div>
    </div>
@stop
