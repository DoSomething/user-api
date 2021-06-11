<nav class="navbar navbar-default">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>

            <a class="navbar-brand" href="/admin">Admin</a>
            <ul class="nav navbar-nav">
                @if (Auth::user())
                    <li @if (Request::is('admin/import-files*')) class="active" @endif>
                        <a class="nav-item nav-link" href="/admin/imports">
                            Imports
                        </a>
                    </li>
                    {{-- <li @if (Request::is('failed-jobs*')) class="active" @endif>
                        <a class="nav-item nav-link" href="{{  '/failed-jobs'  }}">
                            Failed jobs
                        </a>
                    </li> --}}
                @endif
            </ul>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
            <ul class="nav navbar-nav navbar-right">
                <li @if (Request::path() === "admin/imports/create?type=email-subscription") class="active" @endif>
                    <a class="nav-item nav-link" href="/admin/imports/create?type=email-subscription">
                        Email subscriptions
                    </a>
                </li>

                <li @if (Request::path() === "admin/import/mute-promotions") class="active" @endif>
                    <a class="nav-item nav-link" href="/admin/imports/create?type=mute-promotions">
                        Mute promotions
                    </a>
                </li>

                <li @if (strpos(Request::path(), 'admin/rock-the-vote') !== false)) class="active" @endif>
                    <a class="nav-item nav-link" href="/admin/imports/create?type=rock-the-vote">
                        Rock The Vote
                    </a>
                </li>
            </ul>
        </div><!--/.nav-collapse -->
    </div><!--/.container-fluid -->
</nav>
