@section('scripts')
    @parent
    <link href="https://fonts.googleapis.com/css?family=Roboto&display=swap" rel="stylesheet">
@endsection

    <a href="{{ url('google/continue') }}" class="button py-2 google-login" >
        <div class="inline-block">
            @include('icons.new-google-icon')
        </div>
        Continue with Google
    </a>
