@extends('layouts.app', ['extended' => true])

@section('content')
    <div>
        {{-- wondering whether we can just use the cover image template we already have and adjust for the new reg design? --}}
        {{-- <div>
            @yield('profile-form-image')
        </div> --}}
        <div>
            @yield('profile-title')
            @yield('profile-subtitle')
            @yield('profile-form')
        </div>
    </div>
@endsection
