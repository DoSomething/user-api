@extends('profiles.profile')

@section('title', 'Create Account | DoSomething.org')

@section('form-image-url')
    '/images/welcome-form-bg.png'
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

    @if ($social_auth_position === 'position_top')
        <div class="md:flex items-start">
            <div class="mb-4 md:m-0 md:w-1/2">
                @include('auth.google')
            </div>
            <div class="md:w-1/2">
                @include('auth.facebook')
            </div>
        </div>

        <div class="my-6 flex">
            <p class="ml-2 footnote">or</p>
            <hr class="ml-2 mt-2 w-full border-gray-600 border-t-2 border-solid">
        </div>
    @endif

    <form id="profile-register-form" method="POST" action="{{ url('register')}}">
        {{ csrf_field() }}

        <div class="md:flex md:flex-wrap md:justify-between">
            <div class="form-item md:w-1/2 md:pr-4">
                <label for="first_name" class="field-label">First Name</label>
                <input name="first_name" type="text" id="first_name" class="text-field required js-validate" placeholder="First Name" value="{{ old('first_name') }}" autofocus data-validate="first_name" data-validate-required />
            </div>

            <div class="form-item md:w-1/2">
                <label for="last_name" class="field-label">Last Name</label>
                <input name="last_name" type="text" id="last_name" class="text-field required js-validate" placeholder="Last Name" value="{{ old('last_name') }}" data-validate="last_name" data-validate-required />
            </div>
        </div>

        <div class="form-item">
            <label for="email" class="field-label">Email Address</label>
            <input name="email" type="text" id="email" class="text-field required js-validate" placeholder="puppet-sloth@example.org" value="{{ old('email') }}" data-validate="email" data-validate-required />
        </div>

        <div class="form-item password-visibility">
            <label for="password" class="field-label">Password</label>
            <input name="password" type="password" id="password" class="text-field required js-validate" placeholder="6+ characters... make it tricky!" data-validate="password" data-validate-required />
            <span class="password-visibility__toggle -hide"></span>
        </div>

        <div class="form-item -padded">
            <input type="submit" id='register-submit' class="button capitalize" value="Create Account">
        </div>

        <div class="form-item">
            <p class="footnote"><em>Creating an account means you agree to the <a href="https://www.dosomething.org/us/about/terms-service">Terms of Service​</a>, <a href="https://www.dosomething.org/us/about/privacy-policy">Privacy Policy</a> and our default <a href="https://www.dosomething.org/us/about/default-notifications">Notification Settings</a>. DoSomething.org will send you communications; you may change your preferences in your account settings.</em></p>
        </div>
    </form>

    <p class="text-gray-500 mt-5">
        Already have an account? <a class="login-link" href="{{ url('login') }}" data-target="link">Log In</a>
    </p>
@endsection
