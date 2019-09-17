@extends('layouts.app')

@section('content')
    <div style="background-image: url(@yield('form-image-url'))">
        @yield('profile-title')
        @yield('profile-subtitle')
        @yield('profile-form')
    </div>
@endsection
