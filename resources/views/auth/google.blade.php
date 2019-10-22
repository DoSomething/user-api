@section('scripts')
    @parent
    <link href="https://fonts.googleapis.com/css?family=Roboto&display=swap" rel="stylesheet">
@endsection

    <a href="{{ url('google/continue') }}" class="button google-login md:w-3/4" style="font-family:'Roboto'" >
        @include('icons.google-icon')
        Continue with Google
    </a>
