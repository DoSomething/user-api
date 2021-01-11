<dt>{{ $label ?? \Illuminate\Support\Str::title($field) }}:</dt>
<dd>{{ $user->{$field} ?? '—' }}</dd>
