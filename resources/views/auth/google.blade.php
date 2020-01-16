@section('scripts')
    @parent
    <link href="https://fonts.googleapis.com/css?family=Roboto&display=swap" rel="stylesheet">
@endsection

    <a href="{{ url('google/continue') }}" class="button google-login" >
        <div class="hidden md:inline-block">
            @include('icons.google-icon-2')
        </div>
        Continue with Google
    </a>
