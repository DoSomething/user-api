@extends('profiles.profile')

@section('title', 'Create Account | DoSomething.org')

@section('form-image-url')
    '/images/registration-v2-03.svg'
@endsection

@section('profile-title')
    <h2 class="text-black">Create your account</h2>
@endsection
@section('profile-subtitle')
    <p>Ready to officially become a DoSomething member? You’ll make an impact alongside millions of young people, and earn easy scholarships for volunteering.<p>
@endsection

@section('profile-form')
    @if (count($errors) > 0)
        @include('forms.errors', ['errors' => $errors])
    @endif

    <form method="POST" action="{{ url('register-beta')}}">
        {{ csrf_field() }}

        <div class="form-item">
            <label for="first_name" class="field-label">{{ trans('auth.fields.first_name') }}</label>
            <input name="first_name" type="text" id="first_name" class="text-field required js-validate" placeholder="{{ trans('auth.validation.placeholder.first_name') }}" value="{{ old('first_name') }}" autofocus data-validate="first_name" data-validate-required />
        </div>

        <div class="form-item">
            <label for="last_name" class="field-label">{{ trans('auth.fields.last_name') }}</label>
            <input name="last_name" type="text" id="last_name" class="text-field required js-validate" placeholder="{{ trans('auth.validation.placeholder.last_name') }}" value="{{ old('last_name') }}" data-validate="last_name" data-validate-required />
        </div>

        <div class="form-item">
            <label for="email" class="field-label">{{ trans('auth.fields.email') }}</label>
            <input name="email" type="text" id="email" class="text-field required js-validate" placeholder="puppet-sloth@example.org" value="{{ old('email') }}" data-validate="email" data-validate-required />
        </div>

        <div class="form-item password-visibility">
            <label for="password" class="field-label">{{ trans('auth.fields.password') }}</label>
            <input name="password" type="password" id="password" class="text-field required js-validate" placeholder="{{ trans('auth.validation.placeholder.password') }}" data-validate="password" data-validate-required />
            <span class="password-visibility__toggle -hide"></span>
        </div>

        <div class="form-item -padded">
            <input type="submit" class="button" value="Create Account">
        </div>

        <div class="form-item">
            <p class="footnote"><em>Creating an account means you agree to the <a href="https://www.dosomething.org/us/about/terms-service">Terms of Service​</a>, <a href="https://www.dosomething.org/us/about/privacy-policy">Privacy Policy</a> and our default Notification Settings. DoSomething.org will send you communications; you may change your preferences in your account settings.</em></p>
        </div>
    </form>

    <div class="my-6 flex">
        <p class="ml-2 footnote">or</p>
        <hr class="ml-2 mt-2 w-full border-gray-600 border-t-2 border-solid">
    </div>

    @include('auth.facebook')

    <p>Already have an account? <a href="{{ url('login') }}">Log In</a></p>
@endsection
