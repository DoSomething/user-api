@extends('profiles.profile')

@section('title', 'Edit Profile | DoSomething.org')

@section('form-image-url')
    '/images/registration-v2-03.svg'
@endsection

@section('profile-title')
    <h2 class="text-black">Choose your contact method</h2>
@endsection
@section('profile-subtitle')
    <p>What’s the best way to reach you -- email? Phone? Carrier pigeon? (Jkjk.) Let us know and we’ll send you all the best stuff, when and where you want it.<p>
@endsection

@section('profile-form')
    @if (count($errors) > 0)
        <div class="validation-error fade-in-up">
            <h4>{{ trans('auth.validation.issues') }}</h4>
            <ul class="list -compacted">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ url('profile/subscriptions')}}">
        {{ method_field('PATCH') }}
        {{ csrf_field() }}

        <div class="form-item">
            <label for="mobile" class="field-label">Cell Phone # (Optional)</label>
            <input name="mobile" type="text" id="mobile" class="text-field js-validate" placeholder="(555) 555-5555" value="{{ old('mobile') ?: $user->mobile }}" data-validate="phone" />
        </div>
        <div class="form-item">
            <p class="footnote"><em>DoSomething.org weekly updates will be sent to your phone number 1 time per week from 38383. Message and data rates may apply. Text <strong>HELP</strong> to 38383 for help. Text <strong>STOP</strong> to 38383 to opt out. Please review our <a href="https://www.dosomething.org/us/about/terms-service">Terms of Service​</a> and <a href="https://www.dosomething.org/us/about/privacy-policy">Privacy Policy</a> pages. T-Mobile is not liable for delayed or undelivered messages.</em></p>
        </div>

        <p class="font-bold mt-2">Our Email Newsletters</p>
        <p class="mt-1">Community! Scholarships! News! Exclamation points! Our email newsletters are bringing inspiration and education straight to your inbox. Let us know which ones you want.</p>

        <div class="form-item mt-1">
            <label for="community" class="option -checkbox">
                {{-- @TODO: DRY up this 'checked' logic somehow? Integrate this into the checkbox partial? --}}
                <input type="checkbox" name="email_subscription_topics[]" id="community" value="community" class="mt-1" {{in_array("community", (count($errors) ? old('email_subscription_topics') : $user->email_subscription_topics) ?: []) ? "checked" : null}} />
                <span class="font-bold">WYD (What You’re Doing)</span>
                <p class="footnote">Our weekly community newsletter. Learn what DoSomething members are doing to change the world, and how you can join them!</p>
            </label>
            <label for="scholarships" class="option -checkbox">
                <input type="checkbox" name="email_subscription_topics[]" id="scholarships" value="scholarships" class="mt-1" {{in_array("scholarships", (count($errors) ? old('email_subscription_topics') : $user->email_subscription_topics) ?: []) ? "checked" : null}} />
                <span class="font-bold">Pays To Do Good</span>
                <p class="footnote">Our monthly scholarships newsletter. Earn easy scholarships for volunteering, get clutch tips on applying, and read the latest news about education.</p>
            </label>
            <label for="news" class="option -checkbox">
                <input type="checkbox" name="email_subscription_topics[]" id="news" value="news" class="mt-1" {{in_array("news", (count($errors) ? old('email_subscription_topics') : $user->email_subscription_topics) ?: []) ? "checked" : null}} />
                <span class="font-bold">The Breakdown</span>
                <p class="footnote">Our current events newsletter, sent twice a week. Featuring the week’s headlines and ways to impact them, you can read the news *and* change the news.</p>
            </label>
            <label for="lifestyle" class="option -checkbox">
                <input type="checkbox" name="email_subscription_topics[]" id="lifestyle" value="lifestyle" class="mt-1" {{in_array("lifestyle", (count($errors) ? old('email_subscription_topics') : $user->email_subscription_topics) ?: []) ? "checked" : null}} />
                <span class="font-bold">The Boost</span>
                <p class="footnote">Our weekly cause and lifestyle newsletter. You’ll receive one article (like an inspiring story of a young changemaker or a how-to volunteering guide), plus one related action every Thursday.</p>
            </label>
        </div>

        <ul class="form-actions -inline">
            <li>
                <div class="form-actions">
                    <a href="{{ $intended }}" class="button" >Skip</a>
                </div>
            </li>
            <li>
                <div class="form-actions">
                    <input type="submit" class="button" value="Finish">
                </div>
            </li>
        </ul>
    </form>
@endsection
