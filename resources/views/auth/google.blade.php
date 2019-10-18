@section('scripts')
    @parent
    <link href="https://fonts.googleapis.com/css?family=Roboto&display=swap" rel="stylesheet">
@endsection

    <a href="{{ url('google/continue') }}" class="button google-login" style="font-family:'Roboto'" >
        @include('icons.google-icon')
        Continue with Google
    </a>

{{-- <a href="{{ url('google/continue') }}"><img src="/images/btn_google_signin_light_pressed_web@2x.png" /></a> --}}