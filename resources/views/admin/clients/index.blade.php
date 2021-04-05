@extends('admin.layouts.main')

@section('title', 'OAuth Clients')

@section('header_content')
    @include('admin.layouts.header', ['subtitle' => 'Northstar application access & permissions'])
@endsection

@section('main_content')
<div class="container -padded">
  <div class="wrapper">
      <div class="container__block -narrow">
          <div class="gallery__heading"><h1>All Clients</h1></div>
          <p>These are the OAuth clients registered in Northstar and the permissions they have been given. Tokens can be
             used to <a href="https://git.io/fN6yC">authorize requests</a> to any compatible DoSomething.org services.</p>
      </div>
      <ul class="gallery -duo">
          @forelse($clients as $client)
              <li>
                  <article class="figure -left client {{ Str::startsWith($client->client_id, 'dev-') ? '-dev' : null }}">
                      <div class="figure__media">
                          <a href="{{ route('admin.clients.show', [$client->client_id]) }}">
                              <img alt="key" src="/images/{{ $client->allowed_grant === 'authorization_code' ? 'user' : 'machine'}}.svg" />
                          </a>
                      </div>
                      <div class="figure__body">
                          <h4><a href="{{ route('admin.clients.show', [$client->client_id]) }}">{{ $client->client_id }}</a></h4>
                          <span class="footnote">{{ implode(', ', $client->scope) }}</span>
                          @if(Str::startsWith($client->client_id, 'dev-'))
                              <span class="footnote client__hint">Use for development!</span>
                          @endif
                      </div>
                  </article>
              </li>
          @empty
              <h3>No OAuth clients.</h3>
          @endforelse

      </ul>
      <div class="container__block">
          {{ $clients->links() }}
      </div>
      <div class="container__block">
          <a class="button -secondary" href="{{ route('admin.clients.create') }}">New Client</a>
      </div>

	</div>
</div>
@stop
