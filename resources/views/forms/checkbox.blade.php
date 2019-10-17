<label for="{{ $value }}" class="option -checkbox">
    <input type="checkbox" name="{{ $name }}[{{$index}}]" id="{{ $value }}" value="{{ $value }}" {{in_array($value, (count($errors) ? old($name) : $user->{$name}) ?: []) ? "checked" : null}}>
    <span class="option__indicator"></span>
    <span>{{ $label }}</span>
</label>