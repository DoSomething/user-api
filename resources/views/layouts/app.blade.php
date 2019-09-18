<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">

    <title>@yield('title', 'DoSomething.org')</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon-precomposed" href="{{ asset('apple-touch-icon-precomposed.png') }}">

    @include('layouts.google_tag_manager')
    @include('layouts.snowplow')

    <link rel="stylesheet" href="{{ elixir('app.css', 'dist') }}">
    <script src="{{ asset('dist/modernizr.js') }}"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body class="modernizr-no-js">
    <div class="chrome">
        @if (session('status'))
            <div class="messages">{{ session('status') }}</div>
        @endif
        <div class="wrapper">
            @include('layouts.navigation')
            <section class="container -framed {{ isset($extended) && $extended ? '-extended' : '' }} {{ isset($coverImage) && $coverImage ? '-cover-image' : '' }}">
                @include('layouts.cover_image')
                <div class="wrapper -half">
                    @yield('content')
                </div>
            </section>
        </div>
    </div>

    @include('layouts.variables')
    {{ scriptify(auth()->user() ? auth()->user()->id : null, 'NORTHSTAR_ID') }}
    {{ scriptify(get_client_environment_vars(), 'ENV') }}
    {{ scriptify($errors->messages(), 'ERRORS') }}
    <script src="{{ elixir('app.js', 'dist') }}"></script>
</body>

</html>
