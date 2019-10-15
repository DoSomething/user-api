@extends('profiles.profile')

@section('title', 'Edit Profile | DoSomething.org')

@section('form-image-url')
    '/images/about-form-bg.png'
@endsection

@section('profile-title')
    <h2 class="text-black font-source-sans">Complete Your Profile</h2>
@endsection
@section('profile-subtitle')
    <p>We want to customize your DoSomething experience so it's perfect for you. Fill out the next few sections and we'll make it happen.<p>
@endsection

@section('profile-form')

     @if (count($errors) > 0)
        @include('forms.errors', ['errors' => $errors])
    @endif

    <form method="POST" action="{{ url('profile/about') }}">
        <input type="hidden" name="_method" value="PATCH">
        {{ csrf_field() }}

        <div class="form-item flex flex-wrap justify-between md:justify-start">
            <div class="w-1/2">
            {{-- birthday bug, does it have to do the with 'old birthdate???' --}}
                <label for="birthdate" class="field-label">{{ trans('auth.fields.birthday')." (MM/DD/YYYY)" }}</label>
                <input name="birthdate" type="text" id="birthdate" class="text-field js-validate" placeholder="{{ trans('auth.validation.placeholder.birthday') }}" value="{{ count($errors) ? old('birthdate') : format_date($user->birthdate, "m/d/Y") }}" data-validate="birthday" />
            </div>
        </div>

        <div class="form-item flex flex-wrap justify-between md:justify-start">
            <label for="voter_registration_status" class="field-label height-auto w-full">Are you registered to vote at your current address?</label>
            <div class="form-item -reduced w-1/5">
                <label class="option -radio">
                    <input type="radio" name="voter_registration_status" value="confirmed" {{ (old('voter_registration_status') ?: $user->voter_registration_status) === 'confirmed' ? 'checked' : '' }}>
                    <span class="option__indicator"></span>
                    <span>Yes</span>
                </label>
            </div>
            <div class="form-item -reduced w-1/5">
                <label class="option -radio">
                    <input type="radio" name="voter_registration_status" value="unregistered" {{ (old('voter_registration_status') ?: $user->voter_registration_status) === 'unregistered' ? 'checked' : '' }}>
                    <span class="option__indicator"></span>
                    <span>No</span>
                </label>
            </div>
            <div class="form-item -reduced w-3/5 pr-0">
                <label class="option -radio">
                    <input type="radio" name="voter_registration_status" value="uncertain" {{ (old('voter_registration_status') ?: $user->voter_registration_status) === 'uncertain' ? 'checked' : '' }}>
                    <span class="option__indicator"></span>
                    <span>I'm not sure</span>
                </label>
            </div>
        </div>

        <div class="form-item">
            <p class="font-bold mt-2">What cause areas do you care about most?</p>
            <div class="flex flex-wrap" >
                <div class="w-full md:w-1/2">
                    @foreach ( $causes1 as $value => $label )
                            @include('forms.checkbox', ['name' => 'causes', 'value' => $value , 'label' =>  $label])
                    @endforeach
                </div>
                <div class="w-full md:w-1/2">
                    @foreach ( $causes2 as $value => $label )
                            @include('forms.checkbox', ['name' => 'causes', 'value' => $value , 'label' =>  $label])
                    @endforeach
                </div>
                    
            </div>
        </div>

        <div class="flex pt-4">
            <div class="w-1/3 flex justify-start">
                <img src="/images/about-form-icon.svg" />
            </div>
            <div class="w-2/3 flex justify-around md:justify-end p-2">
                <div class="m-1">
                    <a href="{{ url('profile/subscriptions') }}" class="button capitalize -secondary-beta">Skip</a>
                </div>
                <div class="m-1">
                    <input type="submit" id="register-submit" class="button capitalize" value="Next">
                </div>
            </div>
        </div>
        
    </form>
@endsection
