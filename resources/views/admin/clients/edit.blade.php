@extends('admin.layouts.main')

@section('title', 'OAuth Clients')

@section('header_content')
    @include('admin.layouts.header', ['subtitle' => 'Northstar application access & permissions'])
@endsection

@section('main_content')
    <div class="container -padded">
        <div class="wrapper">
            <div class="container__block -narrow">
                <h1>{{ $client->title ?? Str::title($client->client_id) }}</h1>

                @include('forms.errors')

                <form action="{{ route('admin.clients.update', $client->client_id) }}" method="POST">
                    {{ method_field('PUT') }}

                    {{ csrf_field() }}

                    <div class="form-item -padded">
                        <label for="title" class="field-label">Title</label>
                        <input name="title" type="text" class="text-field" value="{{ old('title', $client->title) }}" placeholder="What do we call this application?" />
                    </div>

                    <div class="form-item -padded">
                        <label for="description" class="field-label">Description</label>
                        <textarea name="description" class="text-field" placeholder="Explain what this client is used for!">{{ old('description', $client->description) }}</textarea>
                    </div>

                    <div class="form-item -padded">
                        <label for="allowed_grant" class="field-label">Client Type</label>
                        @include('forms.select', ['name' => 'allowed_grant', 'options' => [
                            'authorization_code' => 'Web (Authorization Code grant)',
                            'client_credentials' => 'Machine (Client Credentials grant)'
                        ], 'value' => $client->allowed_grant])

                        <em class="footnote">Use the Authorization Code grant
                            if a user is logging in and doing things (e.g. the
                            website or an admin app). Use the Client
                            Credentials grant if a computer is acting on it's
                            own (e.g. a cron job or queue worker).</em>
                    </div>

                    <div class="form-item -padded">
                        <label for="redirect_uri" class="field-label">Redirect URL(s)</label>
                        <input name="redirect_uri" type="text" class="text-field" value="{{ array_to_csv(old('redirect_uri', $client->redirect_uri)) }}" placeholder="https://app.dosomething.org/login" />

                        <em class="footnote">Required for Authorization Code
                            grant. This is a comma-separated list of URLs that
                            start the login flow.</em>
                    </div>

                    <div class="form-item -padded">
                        <label for="scope" class="field-label">Allowed Scopes</label>
                        @foreach($scopes as $scope => $details)

                            <label class="option -checkbox">
                                <input type="checkbox" name="scope[{{ $scope }}]" id="{{ $scope }}" value="{{ $scope }}" {{ old('scope[' . $scope . ']', in_array($scope, $client->scope)) ? 'checked' : null }}>
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
                        <input type="submit" class="button" value="Update Client" />
                    </div>
                </form>

            </div>
            <div class="container__block -narrow">
                <br><br><br>
                <div class="danger-zone">
                    <h4 class="danger-zone__heading">Danger Zone&#8482;</h4>
                    <div class="danger-zone__block">
                        <div class="form-item">
                            <label for="role" class="field-label">Delete OAuth Client</label>
                            <p class="footnote">This will <strong>permanently delete</strong> this client, and it will no longer
                            be able to create new access or refresh tokens. All existing access tokens will be valid until their
                            expiration (up to 1 hour).
                        </div>

                        <form method="POST" action="{{ route('admin.clients.destroy', ['client' => $client->client_id]) }}">
                            {{ method_field('DELETE')}}

                            {{ csrf_field() }}

                            <input type="submit" class="button -secondary -danger" value="Delete Client" />
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop
