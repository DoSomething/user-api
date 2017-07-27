@if (request()->query('fb') === 'true')
    <a href="{{ url('facebook/continue') }}" class="button facebook-login">{{ trans('auth.log_in.facebook') }}</a>
@endif
