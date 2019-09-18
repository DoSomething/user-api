{{-- @TODO: This should be limited to the current register form --}}
@if (isset($coverImage) && $coverImage)
    <div class="cover-image" style="background-image: url({{ session('coverImage', asset('members.jpg')) }})"></div>
@endif
