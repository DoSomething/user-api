<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'DoSomething.org')</title>

    <link rel="stylesheet" href="{{ asset('dist/app.css') }}">
{{--    <script src="{{ asset('dist/modernizr.js') }}"></script>--}}
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script type="text/javascript">
        window.addEventListener('message', function(e) {
            if (e.data !== 'sizing?') return;
            e.source.postMessage('sizing:' + document.getElementsByClassName('chrome')[0].offsetHeight, e.origin);
        }, false);
    </script>
</head>

<body class="modernizr-no-js">
<div class="chrome">
    <div class="wrapper">
        @if (!empty($withChrome))
            <nav class="navigation">
                <a class="navigation__logo" href="http://www.dosomething.org"><span>DoSomething.org</span></a>
            </nav>
        @endif
        @yield('content')
    </div>
</div>
</body>

<script src="{{ asset('/dist/app.js') }}"></script>

</html>
