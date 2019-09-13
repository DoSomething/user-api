@extends('layouts.profile_edit')

{{-- @section('title', 'Edit Profile | DoSomething.org') --}}
<div>
    @section('profile-form-image')
        <img src="https://picsum.photos/200/300"/>
    @section('profile-title')
        <h2>A Title!</h2>
    @section('profile-subtitle')
        <p>A subtitle!<p>
    @section('profile-form')
        <form>
            <div class="form-item">
                <label for="first-name" class="field-label">First Name</label>
                <input type="text" id="first-name" class="text-field" name="first_name" value="{{ old('first_name') ?: $user->first_name }}" autofocus />
            </div>
        </form>
<div>