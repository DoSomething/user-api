@section('scripts')
    @parent
    <link href="https://fonts.googleapis.com/css?family=Roboto&display=swap" rel="stylesheet">
@endsection

    <a href="{{ url('google/continue') }}" class="button google-login" >
        <div class="inline-block mr-4">
            @include('icons.google-icon-white')
        </div>
        Continue with Google
    </a>
