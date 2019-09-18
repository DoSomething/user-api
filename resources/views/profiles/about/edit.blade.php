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
    <form>
        <div class="form-item">
            <label for="first-name" class="field-label">First Name</label>
            <input type="text" id="first-name" class="text-field" name="first_name" value="{{ old('first_name') ?: $user->first_name }}" autofocus />
        </div>
    </form>
@endsection
