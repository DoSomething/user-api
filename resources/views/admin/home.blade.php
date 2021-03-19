@extends('admin.layouts.main')

@section('header_content')
    @include('admin.layouts.header', ['subtitle' => 'Let\'s administer this.'])
@endsection

@section('main_content')
    <div class="container -padded">
        <div class="wrapper">
            <div class="container__block -narrow">
                <p>Welcome to the <strong>DoSomething.org admin interface</strong>. If you're looking to
                manage user accounts, campaigns, clubs, or domain redirects, you're in the right place.</p>

                @auth
                    <ul>
                        <li><a href="/admin/campaigns">Campaigns</a> <span class="footnote">- create/edit campaigns, actions, signups, and reportbacks</span></li>
                        <li><a href="/admin/users">Users</a> <span class="footnote">- view/edit user profiles, signups, or posts.</span></li>
                        <li><a href="/admin/clubs">Clubs</a> <span class="footnote">- view/edit DoSomething.org clubs</span></li>
                        <li><a href="/admin/redirects">Redirects</a> <span class="footnote">- view/edit vanity URL redirects <em>(admins only)</em></span></li>
                    </ul>

                    <p>Questions? Check out the <a href="/admin/faq">FAQs</a> or ask in <code>#help-product</code>.</p>
                @endauth

                @guest
                    <p>Drop a message in the <code>#help-product</code> Slack room if you can't log in!</p>

                    <p><a href="/login" class="button">Log In</a></p>
                @endguest
        </div>
    </div>

@stop
