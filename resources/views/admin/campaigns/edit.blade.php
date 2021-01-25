@extends('layouts.master')

@section('main_content')

    @include('layouts.header', ['header' => 'Campaign IDs', 'subtitle' => 'Create & manage campaign IDs.'])

    <div class="container -padded">
        <div class="wrapper">
            <div class="container__block -narrow">
                <h1>Edit {{ $campaign->internal_title }}</h1>
                <br>
                <form method="post" enctype="application/x-www-form-urlencoded" action="{{ route('campaigns.update', $campaign) }}">
                {{ csrf_field()}}
                <input name="_method" type="hidden" value="PATCH">

                    <div class="form-item">
                        <label class="field-label">Internal Campaign Name</label>
                        <input type="text" name="internal_title" class="text-field"
                            @if (old('internal_title'))
                                value="{{ old('internal_title') }}"
                            @else
                                value="{{ $campaign->internal_title }}"
                            @endif
                        >
                    </div>

                @if (is_admin_user())
                    <div class="form-item">
                        <label class="field-label">Contentful Campaign ID <em>(optional)</em></label>
                        <input type="text" name="contentful_campaign_id" class="text-field"
                            @if (old('contentful_campaign_id'))
                                value="{{ old('contentful_campaign_id') }}"
                            @else
                                value="{{ $campaign->contentful_campaign_id }}"
                            @endif
                        >
                        <p class="footnote"><em>If you are creating a campaign and want it to show up on <a href="https://www.dosomething.org/us/campaigns">Explore Campaigns</a> or under “Campaigns” on Cause Hub pages [<a href="https://www.dosomething.org/us/causes/education">example</a>] you must fill this in. <a href="https://user-images.githubusercontent.com/2658867/75452147-bb652080-593f-11ea-8338-188feecad0bd.png">Here’s how you can find the Contentful ID.</a></em></p>
                    </div>
                    <div class="form-item">
                        <label class="field-label">Group Type <em>(optional)</em></label>
                        @include('forms.select', ['options' => $group_types, 'name' => 'group_type_id', 'value' => $campaign->group_type_id, 'optional' => true])
                    </div>
                @endif

                    <div class="form-item">
                        <label class="field-label">Cause Area <em>(choose between 1-5)</em></label>
                        <div class="columns-2">
                            @foreach($causes as $cause => $name)
                                <label class="option -checkbox">
                                    <input {{ in_array($cause, $campaign->cause) ? 'checked' : '' }} name="cause[{{ $cause }}]" type="checkbox" value="{{ $cause }}">
                                    <span class="option__indicator"></span>
                                    <span>{{ $name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="form-item">
                        <label class="field-label">Proof of Impact</label>
                        <input type="text" name="impact_doc" class="text-field"
                            @if (old('impact_doc'))
                                value="{{ old('impact_doc') }}"
                            @else
                                value="{{ $campaign->impact_doc }}"
                            @endif
                        >
                    </div>

                    <div class="form-item">
                        <label class="field-label">Start Date</label>
                        <input type="text" name="start_date" class="text-field"
                            @if (old('start_date'))
                                value="{{ old('start_date') }}"
                            @else
                                value="{{ $campaign->start_date->format('m/d/Y') }}"
                            @endif
                        >
                    </div>

                    <div class="form-item">
                        <label class="field-label">End Date</label>
                        <input type="text" name="end_date" class="text-field" placeholder="e.g. 10/16/2018 or you can leave this blank if there's no end date"
                            @if (old('end_date'))
                                value="{{ old('end_date') }}"
                            @else
                                value="{{ $campaign->end_date ? $campaign->end_date->format('m/d/Y') : null }}"
                            @endif
                        >
                    </div>

                    <ul class="form-actions -inline -padded">
                        <li><input type="submit" class="button" value="Submit"></li>
                    </ul>
                </form>
            </div>
        </div>
    </div>

@stop
