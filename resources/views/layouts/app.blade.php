<!DOCTYPE html>
<html lang="{{ App::getLocale() }}">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="x-ua-compatible" content="ie=edge">

    <title>@yield('title', 'DoSomething.org')</title>

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon-precomposed" href="{{ asset('apple-touch-icon-precomposed.png') }}">

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
            @if (isset($extended) && $extended)
                @include('layouts.navigation', ['extended' => true])
                <section class="container -framed -extended">
                    <div class="cover-photo" style="background-image: url({{ asset('members.jpg') }})"></div>

                    <div class="wrapper -half">
                        @yield('content')
                    </div>
                </section>
            @else
                @include('layouts.navigation', ['extended' => false])
                <section class="container -framed">
                    <div class="wrapper">
                        @yield('content')
                    </div>
                </section>
            @endif
        </div>
    </div>

    @include('layouts.variables')
    <script src="{{ elixir('app.js', 'dist') }}"></script>
    @include('layouts.google_analytics')
</body>

</html>
