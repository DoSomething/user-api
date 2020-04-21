@extends('layouts.app', ['extended' => true])

@section('content')
    <div class="pt-20 md:pt-1/4 lg:pt-0 lg:pl-1/3" style="background-image: url(@yield('form-image-url'))">
        <div class="p-6 bg-white font-source-sans">
            @yield('profile-title')
            @yield('profile-subtitle')
            @yield('profile-form')
        </div>
    </div>
@endsection
