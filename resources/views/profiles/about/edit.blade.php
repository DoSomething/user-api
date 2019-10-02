@extends('profiles.profile')

@section('title', 'Edit Profile | DoSomething.org')

@section('form-image-url')
    '/images/registration-v2-02.svg'
@endsection

@section('profile-title')
    <h2 class="text-black">Complete Your Profile</h2>
@endsection
@section('profile-subtitle')
    <p>We want to customize your DoSomething experience so it's perfect for you. Fill out the next few sections and we'll make it happen.<p>
@endsection

@section('profile-form')
    <form method="POST" action="{{ url('profile/about') }}">
        <input type="hidden" name="_method" value="PATCH">
        {{ csrf_field() }}

        <div class="form-item">
            <label for="birthdate" class="field-label">{{ trans('auth.fields.birthday') }}</label>
            <div class="form-item -reduced">
                <label for="month">Month</label>
                <input class="text-field" name="birthdate[]" type="text" placeholder="MM" maxlength="2"  />
            </div>
            <div class="form-item -reduced">
                <label for="day">Day</label>
                <input class="text-field" name="birthdate[]" type="text" placeholder="DD" maxlength="2" />
            </div>
            <div class="form-item -reduced">
                <label for="year">Year</label>
                <input class="text-field" name="birthdate[]" type="text" placeholder="YYYY" maxlength="4" />
            </div>
        </div>

        <div class="form-item">
            <label for="voter_registration_status" class="field-label height-auto">Are you registered to vote at your current address?</label>
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
        </div>

        <div class="form-item">
            <label for="cause_areas" class="field-label height-auto">What cause areas do you care about most?</label>
            <div class="flex flex-wrap" >
                <div class="w-full md:w-1/2">
                    @foreach ( $causes1 as $value => $label )
                            @include('forms.checkbox', ['name' => 'causes[]', 'value' => $value , 'label' =>  $label])
                    @endforeach
                </div>
                <div class="w-full md:w-1/2">
                    @foreach ( $causes2 as $value => $label )
                            @include('forms.checkbox', ['name' => 'causes[]', 'value' => $value , 'label' =>  $label])
                    @endforeach
                </div>
                    
            </div>
        </div>

        <div class="flex pt-4">
            <div class="w-1/3 flex justify-between md:justify-start bg-blue-200">
                placeholder text 
            </div>
            <div class="w-2/3 flex justify-around md:justify-end p-2">
                <div class="w-1/4 ">
                    <a href="{{ url('profile/subscriptions') }}" class="button ">Skip</a>
                </div>
                <div class="w-1/4">
                    <input type="submit" id="register-submit" class="button" value="Next">
                </div>
            </div>
        </div>
        
    </form>
@endsection
