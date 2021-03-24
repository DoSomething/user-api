<!DOCTYPE html>

<html lang="en" class="h-full">

    <head>
        <meta charset="UTF-8"/>
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>
            {{ isset($title) ? $title . ' | Administration' : 'Administration' }}
        </title>

        <link rel="apple-touch-icon-precomposed" href="/assets/images/apple-touch-icon-precomposed.png">

        <link rel="stylesheet" href="{{ elixir('inertia.css', 'dist') }}">

        <script src="{{ elixir('inertia.js', 'dist') }}" defer></script>
    </head>

    <body class="bg-white min-h-full">
        @inertia
    </body>
</html>
