@extends('profiles.profile')

@section('title', 'Edit Profile | DoSomething.org')

@section('form-image-url')
    '/images/subscription-form-bg.png'
@endsection

@section('profile-title')
    <h2 class="text-black">Choose your contact method</h2>
@endsection
@section('profile-subtitle')
    <p>What’s the best way to reach you -- email? Phone? Carrier pigeon? (Jkjk.) Let us know and we’ll send you all the best stuff, when and where you want it.<p>
@endsection

@section('profile-form')
    @if (count($errors) > 0)
        @include('forms.errors', ['errors' => $errors])
    @endif

    <form id="profile-subscriptions-form" method="POST" action="{{ url('profile/subscriptions')}}">
        {{ method_field('PATCH') }}
        {{ csrf_field() }}

        <div class="form-item">
            <label for="mobile" class="field-label">Cell number to receive texts (Optional)</label>
            <input name="mobile" type="text" id="mobile" class="text-field js-validate" placeholder="(555) 555-5555" value="{{ old('mobile') ?: $user->mobile }}" data-validate="phone" autofocus />
        </div>
        <div class="w-full flex justify-start">
            <div class="form-item pr-6">
                <label class="option -radio">
                    <input type="radio" name="sms_status" value="active" {{ (old('sms_status') ?: $user->sms_status) === 'active' ? 'checked' : '' }}>
                    <span class="option__indicator"></span>
                    <span>Weekly Texts</span>
                </label>
            </div>
            <div class="form-item">
                <label class="option -radio">
                    <input type="radio" name="sms_status" value="less" {{ (old('sms_status') ?: $user->sms_status) === 'less' ? 'checked' : '' }}>
                    <span class="option__indicator"></span>
                    <span>Monthly Texts</span>
                </label>
            </div>
        </div>
        <div class="form-item">
            <p class="footnote"><em>By providing your number, DoSomething.org will send you Weekly (up to 8 msgs/month) or Monthly (up to 4 msgs/month) updates about different social change actions and scholarship opportunities from our number, 38383. Message and data rates may apply. Text <strong>HELP</strong> to 38383 for support; text <strong>STOP</strong> to opt out. Please review our <a href="https://www.dosomething.org/us/about/terms-service">Terms of Service​</a> and <a href="https://www.dosomething.org/us/about/privacy-policy">Privacy Policy</a>. Carriers are not liable for delayed or undelivered messages.</em></p>
        </div>

        <p class="font-bold mt-6">Our Email Newsletters</p>
        <p class="mt-1">Community! Scholarships! News! Exclamation points! Our email newsletters are bringing inspiration and education straight to your inbox. Let us know which ones you want.</p>

        <div class="form-item mt-3">
            <label for="community" class="option -checkbox">
                {{-- @TODO: DRY up this 'checked' logic somehow? Integrate this into the checkbox partial? --}}
                <input type="checkbox" name="email_subscription_topics[0]" id="community" value="community" {{in_array("community", (count($errors) ? old('email_subscription_topics') : $user->email_subscription_topics) ?: []) ? "checked" : null}} />
                <span class="option__indicator"></span>
                <span class="font-bold">WYD (What You’re Doing)</span>
                <p class="footnote">Our weekly community newsletter. Learn what DoSomething members are doing to change the world, and how you can join them!</p>
            </label>
            <label for="scholarships" class="option -checkbox">
                <input type="checkbox" name="email_subscription_topics[1]" id="scholarships" value="scholarships" {{in_array("scholarships", (count($errors) ? old('email_subscription_topics') : $user->email_subscription_topics) ?: []) ? "checked" : null}} />
                <span class="option__indicator"></span>
                <span class="font-bold">Pays To Do Good</span>
                <p class="footnote">Our monthly scholarships newsletter. Earn easy scholarships for volunteering, get clutch tips on applying, and read the latest news about education.</p>
            </label>
            <label for="news" class="option -checkbox">
                <input type="checkbox" name="email_subscription_topics[2]" id="news" value="news" {{in_array("news", (count($errors) ? old('email_subscription_topics') : $user->email_subscription_topics) ?: []) ? "checked" : null}} />
                <span class="option__indicator"></span>
                <span class="font-bold">The Breakdown</span>
                <p class="footnote">Our current events newsletter, sent twice a week. Featuring the week’s headlines and ways to impact them, you can read the news *and* change the news.</p>
            </label>
            <label for="lifestyle" class="option -checkbox">
                <input type="checkbox" name="email_subscription_topics[3]" id="lifestyle" value="lifestyle" {{in_array("lifestyle", (count($errors) ? old('email_subscription_topics') : $user->email_subscription_topics) ?: []) ? "checked" : null}} />
                <span class="option__indicator"></span>
                <span class="font-bold">The Boost</span>
                <p class="footnote">Our weekly cause and lifestyle newsletter. You’ll receive one article (like an inspiring story of a young changemaker or a how-to volunteering guide), plus one related action every Thursday.</p>
            </label>
        </div>

        <div class="flex pt-4">
            <div class="w-1/3 flex justify-start">
                <img src="/images/subscription-form-icon.svg" />
            </div>
            <div class="w-2/3 flex justify-around sm:justify-end p-2">
                <div class="m-1">
                    <a href="{{ $intended }}" class="button capitalize -secondary-beta form-skip">Skip</a>
                </div>
                <div class="m-1">
                    <input type="submit" class="button capitalize" value="Finish">
                </div>
            </div>
        </div>
    </form>
@endsection
