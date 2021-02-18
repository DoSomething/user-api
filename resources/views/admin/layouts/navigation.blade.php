<nav class="navigation -white -floating">
    <a class="navigation__logo" href="/admin">
        <span>DoSomething.org</span>
    </a>

    <div class="navigation__menu">
        @auth
            <ul class="navigation__primary">
                <li>
                    {{-- @TODO: set to create route for now, but should be changed to index. --}}
                    <a href="{{ route('admin.campaigns.create') }}">
                        <strong class="navigation__title">Campaigns</strong>
                        <span class="navigation__subtitle">Review & edit</span>
                    </a>
                </li>

                {{-- <li>
                    <a href="/users">
                        <strong class="navigation__title">Users</strong>
                        <span class="navigation__subtitle">Profiles & search</span>
                    </a>
                </li> --}}

                {{-- @if (Auth::user()->hasRole('admin', 'staff')) --}}
                    <li>
                        <a href="{{ route('admin.users.index') }}">
                            <strong class="navigation__title">Users</strong>
                            <span class="navigation__subtitle">Member profiles</span>
                        </a>
                    </li>
                {{-- @endif --}}

                <li>
                    <a href="#"> {{-- /clubs --}}
                        <strong class="navigation__title">Clubs</strong>
                        <span class="navigation__subtitle">Review & edit</span>
                    </a>
                </li>

                <li>
                    <a href="#"> {{-- /faq --}}
                        <strong class="navigation__title">FAQ</strong>
                        <span class="navigation__subtitle">How do I...</span>
                    </a>
                </li>

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
                <li>
                    <a href="/logout">Log Out</a>
                </li>
            </ul>
        @endauth
    </div>
</nav>
