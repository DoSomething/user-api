@extends('layouts.app')

{{-- @TODO: update padding of container --}}
@section('content')
    <div class="pt-12 md:pt-24 lg:pt-0 lg:pl-24 " style="background-image: url(@yield('form-image-url'))">
        <div class=" pl-6 bg-white font-source-sans ">
            @yield('profile-title')
            @yield('profile-subtitle')
            @yield('profile-form')
        </div>
    </div>
@endsection
