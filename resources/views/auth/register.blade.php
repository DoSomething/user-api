@extends('layouts.app', ['extended' => true])

@section('title', 'Create Account | DoSomething.org')

@section('content')
    <div class="container__block -centered">
        <h1>{{ trans('auth.get_started.create_account') }}</h1>
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

        <form id="profile-registration-form" method="POST" action="{{ url('register') }}">
            <input name="_token" type="hidden" value="{{ csrf_token() }}">

            <div class="form-item">
                <label for="first_name" class="field-label">
                    <div class="validation">
                        <div class="validation__label">{{ trans('auth.fields.first_name') }} <span class="form-required" title="This field is required.">*</span></div>
                        <div class="validation__message"></div>
                    </div>
                </label>
                <input name="first_name" type="text" id="first_name" class="text-field required js-validate" placeholder="{{ trans('auth.validation.placeholder.call_you') }}" value="{{ old('first_name') }}" autofocus data-validate="first_name" data-validate-required />
            </div>

            <div class="form-item">
                <label for="birthdate" class="field-label">
                    <div class="validation">
                        <div class="validation__label">{{ trans('auth.fields.birthday') }} <span class="form-required" title="This field is required.">*</span></div>
                        <div class="validation__message"></div>
                    </div>
                </label>
                <input name="birthdate" type="text" id="birthdate" class="text-field required js-validate" placeholder="{{ trans('auth.validation.placeholder.birthday') }}" value="{{ old('birthdate') }}" data-validate="birthday" data-validate-required />
            </div>

            <div class="form-item">
                <label for="email" class="field-label">
                    <div class="validation">
                        <div class="validation__label">{{ trans('auth.fields.email') }} <span class="form-required" title="This field is required.">*</span></div>
                        <div class="validation__message"></div>
                    </div>
                </label>
                <input name="email" type="text" id="email" class="text-field required js-validate" placeholder="puppet-sloth@example.org" value="{{ old('email') }}" data-validate="email" data-validate-required />
            </div>

            @if (App::getLocale() === 'en')
                <div class="form-item">
                    <label for="mobile" class="field-label">{{ trans('auth.fields.mobile') }} <em>{{ trans('auth.validation.optional') }}</em></label>
                    <input name="mobile" type="text" id="mobile" class="text-field js-validate" placeholder="(555) 555-5555" value="{{ old('mobile') }}" data-validate="phone" />
                </div>
            @endif

            <div class="form-item">
                <label for="password" class="field-label">
                    <div class="validation">
                        <div class="validation__label">{{ trans('auth.fields.password') }} <span class="form-required" title="This field is required.">*</span></div>
                        <div class="validation__message"></div>
                    </div>
                </label>
                <input name="password" type="password" id="password" class="text-field required js-validate" placeholder="{{ trans('auth.validation.placeholder.password') }}" data-validate="password" data-validate-required data-validate-trigger="#password_confirmation" />
            </div>

            <div class="form-item">
                <label for="password_confirmation" class="field-label">
                    <div class="validation">
                        <div class="validation__label">{{ trans('auth.fields.confirm_password') }} <span class="form-required" title="This field is required.">*</span></div>
                        <div class="validation__message"></div>
                    </div>
                </label>
                <input name="password_confirmation" type="password" id="password_confirmation" class="text-field required js-validate" placeholder="{{ trans('auth.validation.placeholder.double_checking') }}" data-validate="match" data-validate-required data-validate-match="#password" />
            </div>

            <div class="form-actions -padded">
                <input type="submit" id="register-submit" class="button" value="{{ trans('auth.log_in.submit') }}">
            </div>
        </form>
    </div>

    <div class="container__block -centered">
        @include('auth.facebook')

        <ul>
            <li><a href="{{ url('login') }}">{{ trans('auth.log_in.existing') }}</a></li>
        </ul>
    </div>

    <div class="container__block -centered">
        <p class="footnote">{{ trans('auth.footnote.create') }} <a href="https://www.dosomething.org/us/about/terms-service">{{ trans('auth.footnote.terms_of_service') }}</a>
            &amp; <a href="https://www.dosomething.org/us/about/privacy-policy">{{ trans('auth.footnote.privacy_policy') }}</a> {{ trans('auth.footnote.messaging') }}</p>
    </div>

@stop
