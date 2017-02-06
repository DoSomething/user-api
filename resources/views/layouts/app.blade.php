<!DOCTYPE html>
<html lang="{{ Session::get('language') }}">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">

    <title>@yield('title', 'DoSomething.org')</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon-precomposed" href="{{ asset('apple-touch-icon-precomposed.png') }}">

    <link rel="stylesheet" href="{{ asset('dist/app.css') }}">
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

            <section class="container -framed">

                <div class="wrapper">

                @yield('content')

                </div>
            </section>

        </div>
    </div>

    @include('layouts.variables')
    <script src="{{ asset('/dist/app.js') }}"></script>
    @include('layouts.google_analytics')
</body>

</html>
