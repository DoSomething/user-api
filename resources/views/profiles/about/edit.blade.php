@extends('profiles.profile')

@section('title', 'Edit Profile | DoSomething.org')

@section('form-image-url')
    'https://picsum.photos/100/200'
@endsection

@section('profile-title')
    <h2>Complete Your Profile</h2>
@endsection
@section('profile-subtitle')
    <p>We want to customize your DoSomething experience so it's perfect for you. Fill out the next few sections and we'll make it happen.<p>
@endsection

@section('profile-form')
    <form style="bg-white font-source-sans" action={{ url('') }}>
        <div class="form-item">
            <label for="birthdate" class="field-label">{{ trans('auth.fields.birthday') }}</label>
            <input name="birthdate" type="text" id="birthdate" class="text-field required js-validate" placeholder="{{ trans('auth.validation.placeholder.birthday') }}" value="{{ old('birthdate') }}" data-validate="birthday" data-validate-required />
        </div>
        <div class="form-item">
            <label for="voter_registration_status" class="field-label height-auto">{{ "Are you registered to vote at your current address?"}}</label>
            <div class="form-item -reduced">
                <label class="option -radio">
                    <input type="radio" name="voter_registration_status" value="confirmed" {{ old('voter_registration_status') === 'confirmed' ? 'checked' : '' }}>
                    <span class="option__indicator"></span>
                    <span>Yes</span>
                </label>
            </div>
            <div class="form-item -reduced">
                <label class="option -radio">
                    <input type="radio" name="voter_registration_status" value="unregistered" {{ old('voter_registration_status') === 'unregistered' ? 'checked' : '' }}>
                    <span class="option__indicator"></span>
                    <span>No</span>
                </label>
            </div>
            <div class="form-item -reduced">
                <label class="option -radio">
                    <input type="radio" name="voter_registration_status" value="uncertain" {{ old('voter_registration_status') === 'uncertain' ? 'checked' : '' }}>
                    <span class="option__indicator"></span>
                    <span>I'm not sure</span>
                </label>
            </div>
            {{-- there should be two buttons inside this div, both that bring you to the next page (how do we differentiate this ie is it just a matter of storing their answers and extending them to the next page?) --}}
        </div>
         <div>
            <div >
                <input type="submit" id="register-submit" class="button" value="{{ trans('auth.log_in.submit') }}">
            </div>
            <div >
                <input type="submit" id="register-submit" class="button" value="{{ trans('auth.log_in.submit') }}">
            </div>
        </div>
    </form>
@endsection
