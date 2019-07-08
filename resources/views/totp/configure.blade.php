@extends('layouts.app')

@section('title', 'Add Two-Factor Device | DoSomething.org')

@section('content')
    <div class="container__block -centered">
        <h2 class="heading -alpha">Enable Two-Factor Authentication</h2>
        <p>Scan the QR code below using an authenticator app
        on your phone (such as <a href="https://support.google.com/accounts/answer/1066447">Google Authenticator</a>):</p>

        <img style="display: inline-block;" src="{{ $qr->writeDataUri() }}">

        <p>Then, enter the generated six-digit code below:</p>
    </div>

    <div class="container__block">

        <form method="POST" action="/totp/configure">
            {{ csrf_field() }}

            <div class="container__block">
                <input type="hidden" name="uri" value="{{ $uri }}" />

                <div class="form-item">
                    <label for="code" class="field-label">Code</label>
                    <input type="number" id="code" class="text-field" name="code" autofocus />
                </div>
            </div>

            <div class="container__block">
                <div class="form-actions">
                    <input type="submit" class="button" value="Verify">
                </div>
            </div>
        </form>
    </div>
@stop
