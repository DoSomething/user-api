<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>
        {{ isset($title) ? $title . ' | Administration' : 'Administration' }}
    </title>

    <link rel="stylesheet" href="{{ elixir('app.css', 'dist') }}">
    <script src="{{ asset('dist/modernizr.js') }}"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body class="modernizr-no-js">
    @if (session('flash'))
        <div class="messages">{{ session('flash') }}</div>
    @endif

    <div class="chrome">
        <div class="wrapper">
            <nav class="navigation -white -floating">
                <a class="navigation__logo" href="/admin"><span>DoSomething.org</span></a>
                <div class="navigation__menu">
                    @auth
                        <ul class="navigation__primary">
                            @if (Auth::user()->hasRole('admin', 'staff'))
                                <li>
                                    <a href="{{ route('admin.users.index') }}">
                                        <strong class="navigation__title">Users</strong>
                                        <span class="navigation__subtitle">Member profiles</span>
                                    </a>
                                </li> 
                            @endif
                            @if (Auth::user()->hasRole('admin'))
                                {{-- <li>
                                    <a href="{{ route('superusers.index') }}">
                                        <strong class="navigation__title">Superusers</strong>
                                        <span class="navigation__subtitle">Admins, staff, etc.</span>
                                    </a>
                                </li> --}}
                                {{-- <li>
                                    <a href="{{ route('clients.index') }}">
                                        <strong class="navigation__title">OAuth Clients</strong>
                                        <span class="navigation__subtitle">Northstar apps</span>
                                    </a>
                                </li> --}}
                                <li>
                                    <a href="{{ route('admin.redirects.index') }}">
                                        <strong class="navigation__title">Redirects</strong>
                                        <span class="navigation__subtitle">Vanity URLs & SEO</span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                        <ul class="navigation__secondary">
                            <li><a href="/auth/logout">Log Out</a> </li>
                        </ul>
                    @endauth
                </div>
            </nav>

            <header class="header" role="banner">
                <div class="wrapper">
                    <h1 class="header__title">
                        @yield('title', 'Administration')
                    </h1>
                    <p class="header__subtitle">
                        @yield('subtitle', '…')
                    </p>
                </div>
            </header>


            @yield('main_content')
        </div>
    </div>

    <script src="{{ elixir('app.js', 'dist') }}"></script>
</body>

</html>
