@extends('layouts.app', ['extended' => true])

@section('content')
    {{-- @TODO: add percentage based width to the tailwind config & adjust lg:pl-24 to 1/3 --}}
    <div class="pt-12 md:pt-24 lg:pt-0 lg:pl-24 " style="background-image: url(@yield('form-image-url'))">
        <div class=" p-6 bg-white font-source-sans ">
            @yield('profile-title')
            @yield('profile-subtitle')
            @yield('profile-form')
        </div>
    </div>
@endsection
