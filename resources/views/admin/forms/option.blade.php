<label class="option -checkbox">
    <input type="checkbox" name="{{ $name }}" {{ old($name, ! empty($value)) ? 'checked' : '' }}>
    <span class="option__indicator"></span>
    <span>{{ $label }}</span>
</label>
