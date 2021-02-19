<header class="header" role="banner">
    <div class="wrapper">

        <h1 class="header__title">{{ $title ?? 'Administration' }}</h1>

        @if (isset($subtitle))
            <p class="header__subtitle">{{ $subtitle }}</p>
        @endif

    </div>
</header>
