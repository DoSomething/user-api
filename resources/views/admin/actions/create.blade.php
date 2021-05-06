@extends('admin.layouts.main')

@section('header_content')
    @include('admin.layouts.header', ['title' => 'Action Creation'])
@endsection

@section('main_content')
    <div class="container -padded">
        <div class="wrapper">
            <div class="container__block -narrow">
                <h1>Create Action</h1>

                <form method="POST" action="{{ route('admin.actions.store', ['campaign_id' => $campaignId]) }}">
                    {{ csrf_field()}}

                    <div class="form-item">
                        <label class="field-label">Action Name</label>
                        @include('admin.forms.text', ['name' => 'name', 'placeholder' => 'Name your action e.g. Teens for Jeans Photo Upload'])
                    </div>

                    <div class="form-item">
                        <label class="field-label">Post Type</label>
                        @include('admin.forms.select', ['name' => 'post_type', 'options' => $postTypes, 'placeholder' => 'What type of post is this?'])
                    </div>

                    <div class="form-item">
                        <label class="field-label">Action Type</label>
                        @include('admin.forms.select', ['name' => 'action_type', 'options' => $actionTypes, 'placeholder' => 'What type of action is this?'])
                    </div>

                    <div class="form-item">
                        <label class="field-label">Time Commitment</label>
                        @include('admin.forms.select', ['name' => 'time_commitment', 'options' => $timeCommitments, 'placeholder' => 'How long will this take?'])
                    </div>

                    <div class="form-item -third">
                        <label class="field-label">CallPower Campaign ID</label>
                        @include('admin.forms.text', ['name' => 'callpower_campaign_id', 'placeholder' => 'e.g. 4 (optional)'])
                    </div>

                    <div class="form-item -third">
                        <label class="field-label">Impact Goal</label>
                        @include('admin.forms.text', ['name' => 'impact_goal', 'placeholder' => 'e.g. 4000 (optional)'])
                    </div>

                    <div class="form-item -third">
                        <label class="field-label">Action Noun</label>
                        @include('admin.forms.text', ['name' => 'noun', 'placeholder' => 'e.g. Jeans'])
                    </div>

                    <div class="form-item -third">
                        <label class="field-label">Action Verb</label>
                        @include('admin.forms.text', ['name' => 'verb', 'placeholder' => 'e.g. Collected'])
                    </div>

                    <div class="form-item">
                        <label class="field-label">Post Details</label>
                        @include('admin.forms.option', ['name' => 'reportback', 'label' => 'Reportback'])
                        @include('admin.forms.option', ['name' => 'civic_action', 'label' => 'Civic Action'])
                        @include('admin.forms.option', ['name' => 'scholarship_entry', 'label' => 'Scholarship Entry'])
                        <label class="option -checkbox">
                            <input type="checkbox" name="volunteer_credit" {{ old("volunteer_credit") ? 'checked' : '' }}>
                            <span class="option__indicator"></span>
                            <span>Volunteer Credit <em>(read more about how Volunteer Credits work <a href="https://docs.google.com/document/d/1QG_jC6bKtzp4wSVuQAKPlinM62ALlyl1XQKZyKdB06g/edit#" target="_blank">here</a>)</em></span>
                        </label>
                        @include('admin.forms.option', ['name' => 'anonymous', 'label' => 'Anonymous'])
                        @include('admin.forms.option', ['name' => 'online', 'label' => 'Online Action'])
                        @include('admin.forms.option', ['name' => 'quiz', 'label' => 'Quiz Action'])
                        @include('admin.forms.option', ['name' => 'collect_school_id', 'label' => 'Collect School ID'])
                    </div>

                    <ul class="form-actions -inline -padded">
                        <li><input type="submit" class="button" value="Create Action"></li>
                    </ul>
                </form>
            </div>
        </div>
    </div>

@stop
