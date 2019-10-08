<div>
    <input type="checkbox" name="{{ $name }}[]" value="{{ $value }}" {{in_array($value, (count($errors) ? old($name) : $user->{$name}) ?: []) ? "checked" : null}}>
    <span>{{ $label }}</span>
</div>


{{-- this one should work but it doesn't... --}}
{{-- <label for="{{ $value }}" class="option -checkbox">
    <input type="checkbox" name="{{ $name }}[]" value="{{ $value }}" {{in_array($value, (count($errors) ? old($name) : $user->{$name}) ?: []) ? "checked" : null}}>
    <span class="option__indicator"></span>
    <span>{{ $label }}</span>
</label> --}}