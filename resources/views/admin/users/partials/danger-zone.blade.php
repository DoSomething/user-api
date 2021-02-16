<div class="danger-zone">
    <h4 class="danger-zone__heading">Danger Zone&#8482;</h4>

    <div class="danger-zone__block">
        <form method="POST" action="{{ route('admin.users.resets.create', ['user' => $user->id]) }}">
            {{ method_field('POST')}}
            {{ csrf_field() }}

            <div class="form-item">  
                <label for="password-reset-type" class="field-label">Send Password Reset</label>

                <select id="password-reset-type" name="type">
                  @foreach ( $passwordResetTypes as $value => $label )
                      <option value="{{$value}}">{{$label}}</option>
                  @endforeach
                </select>

                <p class="footnote">This will email the user a link to reset their password.</p>
            </div>

            <div class="form-actions">
                <button type="submit" class="button -secondary">
                    Send
                </button>
            </div>
        </form>
    </div>

    <div class="danger-zone__block">
        <form method="POST" action="{{ route('admin.users.promotions.destroy', ['user' => $user->id]) }}">
            {{ method_field('DELETE')}}
            {{ csrf_field() }}

            <div class="form-item">  
                <label for="mute-promotions" class="field-label">Mute Promotions</label>

                <p class="footnote">This will delete the user's Customer.io profile.</p>
            </div>

            <div class="form-actions">
                <button type="submit" class="button -secondary">
                    Mute promotions
                </button>
            </div>
        </form>
    </div>

    <div class="danger-zone__block">
        <form method="POST" action="{{ route('admin.users.destroy', ['user' => $user->id]) }}">
            {{ method_field('DELETE')}}
            {{ csrf_field() }}

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
