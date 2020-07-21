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

    <form id="profile-about-form" method="POST" action="{{ url('profile/about') }}">
        <input type="hidden" name="_method" value="PATCH">
        {{ csrf_field() }}

        <div class="form-item flex flex-wrap justify-between md:justify-start">
            <div class="w-1/2">
                <label for="birthdate" class="field-label">Birthday (MM/DD/YYYY)</label>
                <input name="birthdate" type="text" id="birthdate" class="text-field js-validate" placeholder="MM/DD/YYYY" value="{{ old('birthdate') ?: format_date($user->birthdate, "m/d/Y") }}" data-validate="birthday" autofocus />
            </div>
            <div id="voter-reg-cta-pre-registration" class="w-full pt-3 hidden"> 
                <p>
                    Did you know you can pre-register to vote in some states before you're 18? 
                    <a id="voter-reg-pre-registration" target="_blank" rel="noopener noreferrer" href="https://vote.dosomething.org/preregister?r=user:{{$user->id}},source:web,source_details:MembershipFlow_PreReg" >
                    Learn more and pre-register</a>
                </p>
            </div>
        </div>

        <div class="form-item flex flex-wrap justify-between md:justify-start">
            <label for="voter_registration_status" class="field-label height-auto w-full">Are you registered to vote at your current address?</label>
            <div class="voter-reg-status w-1/5">
                <label class="option -radio">
                    <input type="radio" name="voter_registration_status" value="confirmed" {{ (old('voter_registration_status') ?: $user->voter_registration_status) === 'confirmed' ? 'checked' : '' }}>
                    <span class="option__indicator"></span>
                    <span>Yes</span>
                </label>
            </div>
            <div class="voter-reg-status w-1/5">
                <label class="option -radio">
                    <input type="radio" name="voter_registration_status" value="unregistered" {{ (old('voter_registration_status') ?: $user->voter_registration_status) === 'unregistered' ? 'checked' : '' }}>
                    <span class="option__indicator"></span>
                    <span>No</span>
                </label>
            </div>
            <div class="voter-reg-status w-3/5 pr-0">
                <label class="option -radio">
                    <input type="radio" name="voter_registration_status" value="uncertain" {{ (old('voter_registration_status') ?: $user->voter_registration_status) === 'uncertain' ? 'checked' : '' }}>
                    <span class="option__indicator"></span>
                    <span>I'm not sure</span>
                </label>
            </div>
            <div id="voter-reg-cta-unregistered" class="w-full hidden">
                <p>Make your voice heard on the issues that matter to you. Take 2 minutes and 
                    <a id="voter-reg-link" target="_blank" rel="noopener noreferrer" href="https://register.rockthevote.com/registrants/new?partner=37187&email_address={{$user->email}}&home_zip_code={{$user->addr_zip}}&source=user:{{$user->id}},source:web,source_details:NewAccountCreationFlow">
                    register to vote at your current address!</a>
                </p>
            </div>
            <div id="voter-reg-cta-uncertain" class="w-full hidden"> 
                <p>
                    Not sure? We can help! Take 2 minutes and 
                    <a id="voter-reg-status-link-uncertain" target="_blank" rel="noopener noreferrer" href="https://am-i-registered-to-vote.org/dosomething/" >
                    check your voter registration status with Rock The Vote!</a>
                </p>
            </div>
             <div id="voter-reg-cta-confirmed" class="w-full hidden"> 
                <p>
                    If you’ve changed addresses, you may not be registered. 
                    <a id="voter-reg-status-link-confirmed" target="_blank" rel="noopener noreferrer" href="https://am-i-registered-to-vote.org/dosomething/" >
                    Check your status just in case!</a>
                </p>
            </div>
        </div>        

        <div class="form-item">
            <p class="font-bold">What cause areas do you care about most?</p>
            <div class="flex flex-wrap" >
                <div class="w-full md:w-1/2">
                    @foreach ( $causes1 as $value => $label )
                            @include('forms.checkbox', ['name' => 'causes', 'index' => $index1, 'value' => $value , 'label' =>  $label])
                            {{-- @TODO: clean up using a php block for this logic and in lower foreach --}}
                            @php
                                $index1++;
                            @endphp
                    @endforeach
                </div>
                <div class="w-full md:w-1/2">
                    @foreach ( $causes2 as $value => $label )
                            @include('forms.checkbox', ['name' => 'causes', 'index' => $index2, 'value' => $value , 'label' =>  $label])
                            @php
                                $index2++;
                            @endphp
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
                    <a href="{{ url('profile/subscriptions') }}" class="button capitalize -secondary-beta form-skip">Skip</a>
                </div>
                <div class="m-1">
                    <input type="submit" id="register-submit" class="button capitalize" value="Next">
                </div>
            </div>
        </div>

    </form>
@endsection
