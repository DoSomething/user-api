<div class="form-item">
    <label for="mobile" class="field-label">Cell Number to Receive Texts (Optional)</label>

    <input name="mobile" type="text" id="mobile" class="text-field js-validate" placeholder="(555) 555-5555" value="{{ $mobile }}" data-validate="phone" autofocus />
</div>

<div class="w-full flex justify-start">
    <div class="form-item pr-6">
        <label class="option -radio">
            <input type="radio" name="sms_status" value="active" {{ $sms_status === 'active' ? 'checked' : '' }}>

            <span class="option__indicator"></span>

            <span>Weekly {{ !isset($allow_stop) ? 'Texts' : '' }}</span>
        </label>
    </div>

    <div class="form-item pr-6">
        <label class="option -radio">
            <input type="radio" name="sms_status" value="less" {{ $sms_status === 'less' ? 'checked' : '' }}>

            <span class="option__indicator"></span>

            <span>Monthly {{ !isset($allow_stop) ? 'Texts' : '' }}</span>
        </label>
    </div>

    @if ($allow_stop ?? '')
        <div class="form-item">
            <label class="option -radio">
                <input type="radio" name="sms_status" value="stop" {{ $sms_status === 'stop' ? 'checked' : '' }}>

                <span class="option__indicator"></span>

                <span>No Texts</span>
            </label>
        </div>
    @endif
</div>

<div class="form-item">
    <p class="footnote italic">
        By providing your number, DoSomething.org will send you Weekly (up to 8 msgs/month) or Monthly (up to 4 msgs/month) updates about different social change actions and scholarship opportunities from our number, 38383. Message and data rates may apply. Text <strong>HELP</strong> to 38383 for support; text <strong>STOP</strong> to opt out. Please review our <a href="https://www.dosomething.org/us/about/terms-service">Terms of Service​</a> and <a href="https://www.dosomething.org/us/about/privacy-policy">Privacy Policy</a>. Carriers are not liable for delayed or undelivered messages.
    </p>
</div>
