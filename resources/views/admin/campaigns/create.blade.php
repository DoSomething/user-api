@extends('admin.layouts.main')

@section('header_content')
    @include('admin.layouts.header', ['title' => 'Campaign Creation'])
@endsection

@section('main_content')
    <div class="container -padded">
        <div class="wrapper">
            <div class="container__block -narrow">
                <h1>Create Campaign ID</h1>

                <p>Please reach out in the <a href="https://dosomething.slack.com/archives/C09ANFQLA" target="_blank">#help-product</a> channel for help creating a campaign ID for your campaign.</p>

                <br>

                <form method="post" enctype="application/x-www-form-urlencoded" action="{{ route('admin.campaigns.store') }}">
                {{ csrf_field()}}

                    <div class="form-item">
                        <label class="field-label">Internal Campaign Name</label>
                        <input type="text" name="internal_title" class="text-field" placeholder="Campaign Name YYYY-MM Start Date
 e.g. Teens for Jeans 2015-08" value="{{old('internal_title') }}">
                    </div>

                    @if (is_admin_user())
                        <div class="form-item">
                            <label class="field-label">Contentful Campaign ID <em>(optional)</em></label>
                            <input type="text" name="contentful_campaign_id" class="text-field" value="{{ old('contentful_campaign_id') }}">
                            <p class="footnote"><em>If you are creating a campaign and want it to show up on <a href="https://www.dosomething.org/us/campaigns">Explore Campaigns</a> or under “Campaigns” on Cause Hub pages [<a href="https://www.dosomething.org/us/causes/education">example</a>] you must fill this in. <a href="https://user-images.githubusercontent.com/2658867/75452147-bb652080-593f-11ea-8338-188feecad0bd.png">Here’s how you can find the Contentful ID.</a></em></p>
                        </div>
                        <div class="form-item">
                            <label class="field-label">Group Type <em>(optional)</em></label>
                            @include('forms.select', ['options' => $group_types, 'name' => 'group_type_id', 'optional' => true])
                        </div>
                    @endif

                    <div class="form-item">
                        <label class="field-label">Cause Area <em>(choose between 1-5)</em></label>
                        <div class="columns-2">
                            @foreach($causes as $cause => $name)
                                <label class="option -checkbox">
                                    <input {{ old('cause.' . $cause) }} name="cause[{{ $cause }}]" type="checkbox" value="{{ $cause }}">
                                    <span class="option__indicator"></span>
                                    <span>{{ $name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="form-item">
                        <label class="field-label">Proof of Impact</label>
                        <input type="text" name="impact_doc" class="text-field" placeholder="Link to Proof of Impact doc, including https://" value="{{ old('impact_doc') }}">
                    </div>
                    <div class="form-item -half">
                        <label class="field-label">Start Date</label>
                        <input type="text" name="start_date" class="text-field" placeholder="MM/DD/YYYY" value="{{ old('start_date') }}">
                    </div>
                    <div class="form-item -half">
                        <label class="field-label">End Date</label>
                        <input type="text" name="end_date" class="text-field" placeholder="MM/DD/YYYY or blank" value="{{ old('end_date') }}">
                    </div>

                    <p>Each action in your campaign will require a different set of metadata, to determine how it will be treated by Rogue. After creating your campaign, you'll be taken to a Campaign ID page that will allow you to create your actions.</p>

                    <ul class="form-actions -inline -padded">
                        <li><input type="submit" class="button" value="Create Campaign"></li>
                    </ul>
                </form>
            </div>
        </div>
    </div>

@stop
