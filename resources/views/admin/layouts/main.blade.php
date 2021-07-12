<!DOCTYPE html>

<html lang="en" class="modernizr-label-click modernizr-checked">

    <head>
        <meta charset="UTF-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>
            {{ isset($title) ? $title . ' | Administration' : 'Administration' }}
        </title>

        <link rel="apple-touch-icon-precomposed" href="/assets/images/apple-touch-icon-precomposed.png">
        <link rel="stylesheet" href="{{ mix('app.css', 'dist') }}">

        <meta name="csrf-token" content="{{ csrf_token() }}">
    </head>

    <body class="modernizr-no-js">
        @if (session('flash'))
            <div class="messages">{{ session('flash') }}</div>
        @endif

        @if ($errors->any())
            <div class="messages">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="chrome">
            <div class="wrapper">

                @include('admin.layouts.navigation')

                @section('header_content')
                @show

                @yield('main_content')
            </div>
        </div>

        {{-- {{ isset($state) ? scriptify($state) : scriptify() }} --}}
        <script src="{{ mix('app.js', 'dist') }}"></script>
    </body>

    {{-- {{ scriptify($auth, 'AUTH') }} --}}
    {{-- {{ scriptify(['GRAPHQL_URL' => config('services.graphql.url')], 'ENV') }} --}}
</html>
