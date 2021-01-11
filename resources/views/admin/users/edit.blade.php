@extends('layouts.admin')

@section('title', 'Users')
@section('subtitle', 'View & edit member profiles.')

@section('main_content')

<div class ="container -padded">
  <div class="wrapper">
    <div class="container__block -narrow">
      <h1>{{ $user->display_name }}</h1>

      @include('forms.errors')

      <form method="POST" action="{{ route('admin.users.update', ['user' => $user->id]) }}">
        {{ csrf_field() }}
        {{ method_field('PATCH') }}

        <div class="form-item -padded">
          <label for="first_name" class="field-label">First Name</label>
          <input name="first_name" type="text" class="text-field" value="{{ old('first_name', $user->first_name) }}" />
        </div>
        <div class="form-item -padded">
          <label for="last_name" class="field-label">Last Name</label>
          <input name="last_name" type="text" class="text-field" value="{{ old('last_name', $user->last_name) }}" />
        </div>
        <div class="form-item -padded">
          <label for="email" class="field-label">Email</label>
          <input name="email" type="text" class="text-field" value="{{ old('email', $user->email) }}" />
        </div>
        <div class="form-item -padded">
          <label for="mobile" class="field-label">Mobile</label>
          <input name="mobile" type="text" class="text-field" value="{{ old('mobile', $user->mobile) }}" />
        </div>
        <div class="form-item -padded">
          <label for="birthdate" class="field-label">Birthdate</label>
          <input name="birthdate" type="text" class="text-field" value="{{ old('birthdate', $user->birthdate) }}" />
        </div>
        <div class="form-item -padded">
          <label for="addr_street1" class="field-label">Address Street 1</label>
          <input name="addr_street1" type="text" class="text-field" value="{{ old('addr_street1', $user->addr_street1) }}" />
        </div>
        <div class="form-item -padded">
          <label for="addr_street2" class="field-label">Address Street 2</label>
          <input name="addr_street2" type="text" class="text-field" value="{{ old('addr_street2', $user->addr_street2) }}" />
        </div>
        <div class="form-item -padded">
          <label for="addr_city" class="field-label">City</label>
          <input name="addr_city" type="text" class="text-field" value="{{ old('addr_city', $user->addr_city) }}" />
        </div>
        <div class="form-item -padded">
          <label for="addr_state" class="field-label">State</label>
          <input name="addr_state" type="text" class="text-field" value="{{ old('addr_state', $user->addr_state) }}" />
        </div>
        <div class="form-item -padded">
          <label for="addr_zip" class="field-label">Zip Code</label>
          <input name="addr_zip" type="text" class="text-field" value="{{ old('addr_zip', $user->addr_zip) }}" />
        </div>
        <div class="form-item -padded">
          <label for="country" class="field-label">Country</label>
          <div class="select">
            @include('forms.select', [
              'name' => 'country',
              'value' => $user->country,
              'options' => get_countries(),
            ])
          </div>
        </div>
        <div class="form-item -padded">
          <label for="sms_status" class="field-label">SMS Status</label>
          <div class="select">
            @include('forms.select', [
              'name' => 'sms_status',
              'value' => $user->sms_status,
              'options' => [
                  'active' => 'Active Subscriber',
                  'less' => 'Active Subscriber (Less)',
                  'stop' => 'Unsubscribed (texted STOP)',
                  'undeliverable' => 'Undeliverable',
                  'unknown' => 'Unknown Issue'
              ]])
          </div>
        </div>
        <div class="form-item -padded">
          <label for="email_subscription_status" class="field-label">Email Subscription Status</label>
          <div class="select">
            @include('forms.select', [
              'name' => 'email_subscription_status',
              'value' => $user->email_subscription_status,
              'options' => [
                  true => 'Subscribed',
                  false => 'Unsubscribed',
              ]])
          </div>
        </div>
        <div class="form-item -padded">
          <label for="voter_registration_status" class="field-label">Voter Registration Status</label>
          <div class="select">
            @include('forms.select', [
              'name' => 'voter_registration_status',
              'value' => $user->voter_registration_status,
              'options' => [
                  'uncertain' => 'Uncertain',
                  'ineligible' => 'Ineligible',
                  'unregistered' => 'Unregistered',
                  'confirmed' => 'Confirmed',
                  'registration_complete' => 'Registration Complete',
              ]])
          </div>
        </div>
        <div class="form-item -padded">
          <label for="school_id" class="field-label">School ID</label>
          <input name="school_id" type="text" class="text-field" value="{{ old('school_id', $user->school_id) }}" />
        </div>
        <div class="form-item -padded">
          <label for="club_id" class="field-label">Club ID</label>
          <input name="club_id" type="text" class="text-field" value="{{ old('club_id', $user->club_id) }}" />
        </div>
        <div class="form-item -padded">
          <label for="email_subscription_topics" class="field-label">Email Subscription Topics</label>
          @include('forms.checkbox', ['name' => 'email_subscription_topics', 'index' => 'community', 'value' => 'community', 'label' => 'Community (WYD)'])
          @include('forms.checkbox', ['name' => 'email_subscription_topics', 'index' => 'lifestyle', 'value' => 'lifestyle', 'label' => 'Lifestyle (The Boost)'])
          @include('forms.checkbox', ['name' => 'email_subscription_topics', 'index' => 'news', 'value' => 'news', 'label' => 'News (The Breakdown)'])
          @include('forms.checkbox', ['name' => 'email_subscription_topics', 'index' => 'scholarships', 'value' => 'scholarships', 'label' => 'Scholarships (Pays To Do Good)'])
          @include('forms.checkbox', ['name' => 'email_subscription_topics', 'index' => 'clubs', 'value' => 'clubs', 'label' => 'Clubs'])
        </div>
        <div class="form-item -padded">
          <label for="causes" class="field-label">Cause Interests</label>
          @foreach ( $causes as $value => $label )
            @include('forms.checkbox', ['name' => 'causes', 'index' => $value, 'value' => $value , 'label' =>  $label])
          @endforeach
        </div>
        <div class="form-item -padded">
          <label for="feature_flags" class="field-label">Feature Flags</label>
          {{-- NOTE: We use custom checkbox markup here to deal with this object of true/false values: --}}
          @foreach(['badges', 'refer-friends', 'refer-friends-scholarship'] as $flag)
            <label for="feature_flags_{{$flag}}" class="option -checkbox">
                <input type="checkbox" name="feature_flags[{{$flag}}]" id="feature_flags_{{$flag}}" value="true" {{old("feature_flags[$flag]", !empty($user->feature_flags[$flag])) ? "checked" : null}}>
                <span class="option__indicator"></span>
                <span>{{ $flag }}</span>
            </label>
          @endforeach
        </div>
        @if (auth()->user()->hasRole('admin'))
          <div class="form-item -padded">
            <label for="role" class="field-label">Role</label>
            @include('forms.select', [
              'name' => 'role',
              'value' => $user->role,
              'options' => [
                'user' => 'User (default)',
                'staff' => 'Staff',
                'admin' => 'Administrator',
            ]])
          </div>
        @endif
        <div class="form-actions">
          <input type="submit" class="button" value="Save Changes" />
        </div>
      </form>
    </div>
  </div>
</div>
@stop
