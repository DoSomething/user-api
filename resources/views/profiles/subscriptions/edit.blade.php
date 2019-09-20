@extends('profiles.profile')

@section('title', 'Edit Profile | DoSomething.org')

@section('form-image-url')
    'https://picsum.photos/100/200'
@endsection

@section('profile-title')
    <h2 class="text-black">A Title!</h2>
@endsection
@section('profile-subtitle')
    <p>A subtitle!<p>
@endsection

@section('profile-form')
    <form>
        <div class="form-item">
            <label for="first-name" class="field-label">First Name</label>
            <input type="text" id="first-name" class="text-field" name="first_name" value="{{ old('first_name') ?: $user->first_name }}" autofocus />
        </div>
    </form>
@endsection
