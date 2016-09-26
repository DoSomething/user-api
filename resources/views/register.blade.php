@extends('app')

@section('title', 'Create Account | DoSomething.org')

@section('content')
    <div class="container -padded">
        <div class="wrapper">
            <div class="container__block -centered">
                <h1>Create a DoSomething.org account to get started!</h1>
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
                <form method="POST" action="{{ url('register') }}">
                    <input name="_token" type="hidden" value="{{ csrf_token() }}">

                    <div class="form-item">
                        <label for="first_name" class="field-label">First Name</label>
                        <input name="first_name" type="text" class="text-field" placeholder="What do we call you?"value="{{ old('first_name') }}">
                    </div>

                    <div class="form-item">
                        <label for="birthdate" class="field-label">Birthday</label>
                        <input name="birthdate" type="text" class="text-field" placeholder="MM/DD/YYYY" value="{{ old('birthdate') }}">
                    </div>

                    <div class="form-item">
                        <label for="email" class="field-label">Email address</label>
                        <input name="email" type="text" class="text-field" placeholder="puppet-sloth@example.org" value="{{ old('email') }}">
                    </div>

                    <div class="form-item">
                        <label for="mobile" class="field-label">Cell Number <em>(optional)</em></label>
                        <input name="mobile" type="text" class="text-field" placeholder="(555) 555-5555" value="{{ old('mobile') }}">
                    </div>

                    <div class="form-item">
                        <label for="password" class="field-label">Password</label>
                        <input name="password" type="password" class="text-field" placeholder="6+ characters... make it tricky!">
                    </div>

                    <div class="form-item">
                        <label for="password_confirmation" class="field-label">Confirm Password</label>
                        <input name="password_confirmation" type="password" class="text-field" placeholder="Just double checking!">
                    </div>

                    <div class="form-actions -padded">
                        <input type="submit" class="button" value="Create New Account">
                    </div>
                </form>
            </div>
            <div class="container__block -centered">
                <ul>
                    <li><a href="{{ url('login?chrome=' . request()->query('chrome', 'true')) }}">Log in to an existing account</a></li>
                </ul>
            </div>
        </div>
    </div>
@stop
