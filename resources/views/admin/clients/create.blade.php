@extends('admin.layouts.main')

@section('title', 'OAuth Clients')

@section('header_content')
    @include('admin.layouts.header', ['subtitle' => 'Northstar application access & permissions'])
@endsection

@section('main_content')
    <div class="container -padded">
        <div class="wrapper">
            <div class="container__block -narrow">
                <h1>Create Client</h1>

                @include('forms.errors')

                <form action="{{ route('admin.clients.store') }}" method="POST">
                    {{ csrf_field() }}

                    <div class="form-item -padded">
                        <label for="client_id" class="field-label">Client ID</label>
                        <input name="client_id" type="text" class="text-field" value="{{ old('client_id') }}" placeholder="client-id" />
                        <span class="footnote">Careful, this cannot be changed later!</span>
                    </div>

                    <div class="form-item -padded">
                        <label for="client_secret" class="field-label">Client Secret</label>
                        <input name="client_secret" type="text" class="text-field" value="<randomly generated>" disabled />
                    </div>

                    <h3>About this client:</h3>
                    <div class="form-item -padded">
                        <label for="title" class="field-label">Title</label>
                        <input name="title" type="text" class="text-field" value="{{ old('title') }}" placeholder="What do we call this application?" />
                    </div>

                    <div class="form-item -padded">
                        <label for="description" class="field-label">Description</label>
                        <textarea name="description" class="text-field" value="{{ old('description') }}" placeholder="Explain what this client is used for!"></textarea>
                    </div>

                    <h3>Privileges:</h3>
                    <div class="form-item -padded">
                        <label for="allowed_grant" class="field-label">Client Type</label>
                        @include('forms.select', ['name' => 'allowed_grant', 'options' => [
                            'authorization_code' => 'Web (Authorization Code grant)',
                            'client_credentials' => 'Machine (Client Credentials grant)'
                        ]])

                        <em class="footnote">Use the Authorization Code grant
                            if a user is logging in and doing things (e.g. the
                            website or an admin app). Use the Client
                            Credentials grant if a computer is acting on it's
                            own (e.g. a cron job or queue worker).</em>
                    </div>

                    <div class="form-item -padded">
                        <label for="redirect_uri" class="field-label">Redirect URL(s)</label>
                        <input name="redirect_uri" type="text" class="text-field" value="{{ old('redirect_uri') }}" placeholder="https://app.dosomething.org/login" />

                        <em class="footnote">Required for Authorization Code
                            grant. This is a comma-separated list of URLs that
                            start the login flow.</em>
                    </div>

                    <div class="form-item -padded">
                        <label for="scope" class="field-label">Allowed Scopes</label>
                        @foreach($scopes as $scope => $details)
                            <label class="option -checkbox">
                                <input type="checkbox" name="scope[{{ $scope }}]" id="{{ $scope }}" value="{{ $scope }}">
                                <span class="option__indicator"></span>
                                <span><strong>{{ $scope }}</strong> – {{ $details['description'] }}
                                    @if(! empty($details['hint']))
                                        <em class="footnote">
                                            {{ $details['hint'] }}
                                        </em>
                                    @endif
                                </span>
                            </label>
                        @endforeach
                    </div>

                    <div class="form-actions">
                        <input type="submit" class="button" value="Create Client" />
                    </div>
                </form>
            </div>
        </div>
    </div>

@stop
