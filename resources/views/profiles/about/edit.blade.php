@extends('profiles.profile')

@section('title', 'Edit Profile | DoSomething.org')

@section('form-image-url')
    'https://picsum.photos/100/200'
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
                <input class="text-field" name="month" type="text" placeholder="MM" />
            </div>
            <div class="form-item -reduced">
                <label for="day">Day</label>
                <input class="text-field" name="day" type="text" placeholder="DD" />
            </div>
            <div class="form-item -reduced">
                <label for="year">Year</label>
                <input class="text-field" name="year" type="text" placeholder="YYYY" />
            </div>
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
        </div>

        {{-- @TODO: Refactor to loop through list of causes to create the different options --}}
        <div class="form-item">
            <label for="cause_areas" class="field-label height-auto">{{"What cause areas do you care about most?"}}</label>

            <div>
                <input type="checkbox" name="animal_welfare" value="animal_welfare">
                <span>Animal Welfare</span>
            </div>
            <div>
                <input type="checkbox" name="bullying" value="bullying">
                <span>Bullying</span>
            </div>
            <div>
                <input type="checkbox" name="education" value="education">
                <span>Education</span>
            </div>
            <div>
                <input type="checkbox" name="environment" value="environment">
                <span>Environment</span>
            </div>
            <div>
                <input type="checkbox" name="gender_rights_equality" value="gender_rights_equality">
                <span>Gender Rights & Equality</span>
            </div>
            <div>
                <input type="checkbox" name="homelessness_poverty" value="homelessness_poverty">
                <span>Homelessness & Poverty</span>
            </div>
            <div>
                <input type="checkbox" name="immigration_refugees" value="immigration_refugees">
                <span>Immigration & Refugees</span>
            </div>
            <div>
                <input type="checkbox" name="lgbtq_rights_equality" value="lgbtq_rights_equality">
                <span>LGBTQ+ Rights & Equality</span>
            </div>
            <div>
                <input type="checkbox" name="mental_health" value="mental_health">
                <span>Mental Health</span>
            </div>
            <div>
                <input type="checkbox" name="physical_health" value="physical_health">
                <span>Physical Health</span>
            </div>
             <div>
                <input type="checkbox" name="racial_justice_equity" value="racial_justice_equity">
                <span>Racial Justice & Equity</span>
            </div>
             <div>
                <input type="checkbox" name="sexual_harassment_assault" value="sexual_harassment_assault">
                <span>Sexual Harassment & Assault</span>
            </div>
        </div>

        <div class="form-actions -padded -left">
            <a href="{{ url('profile/subscriptions') }}" class="button">Skip</a>
        </div>
        <div class="form-actions -padded -right">
            <input type="submit" id="register-submit" class="button" value="Next">
        </div>
        
    </form>
@endsection
