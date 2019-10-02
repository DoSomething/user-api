<div class="validation-error fade-in-up">
    <h4>{{ trans('auth.validation.issues') }}</h4>
    <ul class="list -compacted">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
