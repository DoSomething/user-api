@section('scripts')
    @parent
    <link href="https://fonts.googleapis.com/css?family=Roboto&display=swap" rel="stylesheet">
@endsection

    <a href="{{ url('google/continue') }}" class="button google-login flex justify-start" >
        <div class="inline-block ml-0 mr-4">
            @include('icons.new-google-icon')
        </div>
        Continue with Google
    </a>
