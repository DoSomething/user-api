@extends('layouts.app')

@section('title', 'Edit Profile | DoSomething.org')

@section('content')
    <div class="container__block -centered">
        <h2 class="heading -alpha">Edit your profile</h2>
    </div>

    @if (count($errors) > 0)
        <div class="container__block">
            <div class="validation-error fade-in-up">
                <h4>Hmm, there were some issues with that submission:</h4>
                <ul class="list -compacted">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('users.update', $user->id) }}">
        {{ method_field('PATCH') }}
        {{ csrf_field() }}

        <div class="container__block">
            <h3 class="heading">Info</h3>

            {{ field('text', 'first_name', 'First Name', $user->first_name) }}
            {{ field('text', 'last_name', 'Last Name', $user->last_name) }}
            {{ field('email', 'email', 'Email', $user->email) }}

            <div class="form-item">
                <label for="mobile" class="field-label">Cell #</label>
                <input type="text" id="mobile" class="text-field" name="mobile" value="{{ old('mobile') ?: $user->mobile }}" />
            </div>

            {{-- @TODO: deal w/ the date input formatting --}}
            <div class="form-item">
                <label for="birthdate" class="field-label">Birthday</label>
                <input type="date" id="birthdate" class="text-field" name="birthdate" value="{{ old('birthdate') ?: format_date($user->birthdate) }}" />
            </div>

            <div class="form-item">
                <label for="addr-street1" class="field-label">Address Street 1</label>
                <input type="text" id="addr-street1" class="text-field" name="addr_street1" value="{{ old('addr_street1') ?: $user->addr_street1 }}" />
            </div>

            <div class="form-item">
                <label for="addr-street2" class="field-label">Address Street 2</label>
                <input type="text" id="addr-street2" class="text-field" name="addr_street2" value="{{ old('addr_street2') ?: $user->addr_street2 }}" />
            </div>

            <div class="form-item">
                <label for="addr-city" class="field-label">City</label>
                <input type="text" id="addr-city" class="text-field" name="addr_city" value="{{ old('addr_city') ?: $user->addr_city }}" />
            </div>

            <div class="form-item">
                <label for="addr-state" class="field-label">State</label>
                <input type="text" id="addr-state" class="text-field" name="addr_state" value="{{ old('addr_state') ?: $user->addr_state }}" />
            </div>

            <div class="form-item">
                <label for="addr-zip" class="field-label">ZIP</label>
                <input type="text" id="addr-zip" class="text-field" name="addr_zip" value="{{ old('addr_zip') ?: $user->addr_zip }}" />
            </div>

            <div class="form-item">
                <label for="country" class="field-label">Country</label>
                <input type="text" id="country" class="text-field" name="country" value="{{ old('country') ?: $user->country }}" />
            </div>
        </div>

        <div class="container__block">
            <h3 class="heading">Change Password</h3>

            <div class="form-item">
                <label for="password" class="field-label">New Password</label>
                <input type="password" id="password" class="text-field" name="password" placeholder="6+ characters... make it tricky!">
            </div>

            <div class="form-item">
                <label for="password-confirmation" class="field-label">Confirm Password</label>
                <input type="password" id="password-confirmation" class="text-field" name="password_confirmation" placeholder="Just double checking!">
            </div>
        </div>

        <div class="container__block">
            <div class="form-actions">
                <input type="submit" class="button" value="Save">
            </div>
            <ul class="form-actions">
                <li><a href="{{ url('users/'.$user->id) }}">Cancel</a></li>
            </ul>
        </div>
    </form>
@stop
