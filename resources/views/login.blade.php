@extends('app')

@section('title', 'Log In | DoSomething.org')

@section('content')
    <div class="container">
        <div class="wrapper">
            <div class="container__block -centered">
                <h1>Log in to get started!</h1>
            </div>
            <div class="container__block -centered">
                @if (count($errors) > 0)
                    <div class="validation-error fade-in-up">
                        <h4>Hmm, there were some issues with that submission:</h4>
                        <ul class="list -compacted">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form method="POST" action="{{ url('login') }}">
                    <input name="_token" type="hidden" value="{{ csrf_token() }}">

                    <div class="form-item">
                        <label for="username" class="field-label">Email address or cell number</label>
                        <input name="username" type="text" class="text-field" placeholder="puppet-sloth@example.org" value="{{ old('username') }}">
                    </div>

                    <div class="form-item">
                        <label for="password" class="field-label">Password</label>
                        <input name="password" type="password" class="text-field" placeholder="••••••••">
                    </div>

                    <div class="form-actions -padded">
                        <input type="submit" class="button" value="Log In">
                    </div>
                </form>
            </div>
            <div class="container__block -centered">
                <ul>
                    <li><a href="{{ url('register?chrome=' . request()->query('chrome', 'true')) }}">Create a DoSomething.org account</a></li>
                    <li><a href="{{ url(config('services.drupal.url').'/user/password') }}">Forgot your password?</a></li>
                </ul>
            </div>
        </div>
    </div>
@stop
