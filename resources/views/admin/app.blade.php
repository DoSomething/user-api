<!DOCTYPE html>

<html lang="en" class="modernizr-label-click modernizr-checked">

    <head>
        <meta charset="UTF-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>
            {{ isset($title) ? $title . ' | Administration' : 'Administration' }}
        </title>

        <link rel="apple-touch-icon-precomposed" href="/assets/images/apple-touch-icon-precomposed.png">
        <link rel="stylesheet" href="{{ elixir('admin.css', 'dist') }}">

        <script src="{{ asset('dist/modernizr.js') }}"></script>
    </head>

    <body class="modernizr-no-js">

        <div class="chrome">
            <div class="wrapper">
                @include('admin.layouts.navigation')

                <div id="app">
                    <div class="flex justify-center p-6 placeholder">
                        <div class="spinner"></div>
                    </div>
                </div>
            </div>
        </div>

        <script src="{{ elixir('admin.js', 'dist') }}"></script>
    </body>

    {{ scriptify($auth, 'AUTH') }}
    {{ scriptify(['GRAPHQL_URL' => config('services.graphql.url')], 'ENV') }}
</html>
