@extends('admin.layouts.main')

@section('header_content')
    @include('admin.layouts.header', ['subtitle' => 'Let\'s administer this.'])
@endsection

@section('main_content')
    <div class="container -padded">
        <div class="wrapper">
            <div class="container__block -narrow">
                <p>Welcome to the <strong>DoSomething.org admin interface</strong>. If you're looking to
                manage user accounts or domain redirects, you're in the right place.</p>

                @auth
                    <p><mark><strong>Currently under construction!</strong></mark> If you
                    run into any issues, head back over to Aurora or Rogue.</p>
                @endauth

                @guest
                    <p>Drop a message in the <code>#help-product</code> Slack room if you can't log in!</p>

                    <p><a href="/login" class="button">Log In</a></p>
                @endguest
        </div>
    </div>

@stop
