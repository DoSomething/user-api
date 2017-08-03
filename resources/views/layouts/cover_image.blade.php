@if (is_container_extended($extended))
    <div class="cover-image" style="background-image: url({{ session('coverImage', asset('members.jpg')) }})"></div>
@endif
