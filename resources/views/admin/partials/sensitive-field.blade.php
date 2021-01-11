<dt>{{ $label ?? Illuminate\Support\Str::title($field) }}:</dt>
@if (Illuminate\Support\Str::contains(request()->query('include'), $field))
    <dd class="revealed">{{ $user->{$field} }} {{ revealer($field) }}
@elseif ($user->{$preview_field})
    <dd>{{ $user->{$preview_field} }}{{ $preview_suffix ?? '' }} {{ revealer($field) }}</dd>
@else
    <dd>&mdash;</dd>
@endif
