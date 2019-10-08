<div>
    <input type="checkbox" name="{{ $name }}[]" value="{{ $value }}" {{in_array($value, (count($errors) ? old($name) : $user->{$name}) ?: []) ? "checked" : null}}>
    <span>{{ $label }}</span>
</div>