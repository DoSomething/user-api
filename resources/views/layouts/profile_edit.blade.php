@extends('layouts.app')


@section('content')
    <div>
        <div>
            @yield('profile-form-image')
        </div>
        <div>
            @yield('profile-title')
            @yield('profile-subtitle')
            @yield('profile-form')
        </div>
    </div>