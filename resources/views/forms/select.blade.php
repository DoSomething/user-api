<div class="select">
    <select name="{{ $name }}">
        <option value="" {{ ! old($name) && ! isset($value) ? 'selected' : '' }} {{ !isset($optional) ? 'disabled' : ''}}>{{ $placeholder ?? '–' }}</option>
        @foreach($options as $option => $label)
            @if (old($name))
                <option value="{{ $option }}" {{ old($name) === $option ? 'selected': '' }}>{{ $label }}</option>
            @else
                <option value="{{ $option }}" {{ isset($value) && $value === $option ? 'selected': '' }}>{{ $label }}</option>
            @endif
        @endforeach
    </select>
</div>
