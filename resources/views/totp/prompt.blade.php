@extends('layouts.app')

@section('title', 'Verify | DoSomething.org')

@section('content')
    <div class="container__block -centered">
        <h2 class="heading -alpha">🔐</h2>
    </div>
    <div class="container__block -centered">
        <p><strong>This account is protected by two-factor authentication.</strong> To continue, use your authenticator app to generate a code:</p>
    </div>

    @if (count($errors) > 0)
        <div class="container__block">
            <div class="validation-error fade-in-up">
                <h4>Hmm, there were some issues with that submission:</h4>
                <ul class="list -compacted">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <form method="POST" action="/totp">
        {{ csrf_field() }}

        <div class="container__block">
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
@stop
