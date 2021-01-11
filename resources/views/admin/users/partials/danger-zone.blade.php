<div class="danger-zone">
    <h4 class="danger-zone__heading">Danger Zone&#8482;</h4>
    <div class="danger-zone__block">
        <form method="POST" action="{{ route('admin.users.destroy', ['user' => $user->id]) }}">
            {{ method_field('DELETE')}}
            <div class="form-item">
                <label for="id" class="field-label">Delete Account</label>
                <p class="footnote">This will <strong>permanently destroy</strong> this user's Northstar & Customer.io profiles, Rogue campaign activity, and Gambit conversation history.</p>
            </div>
            <div class="form-actions">
                <button type="submit" class="button -secondary -danger" data-confirm="Are you sure you want to immediately & permanently destroy all of {{ $user->display_name}}'s data? THIS CANNOT BE UNDONE.">
                    Delete Immediately
                </button>
            </div>
        </form>
    </div>
</div>
