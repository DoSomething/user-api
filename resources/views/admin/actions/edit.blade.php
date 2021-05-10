@extends('admin.layouts.main')

@section('header_content')
    @include('admin.layouts.header', ['title' => 'Edit Action'])
@endsection

@section('main_content')
    <div class="container -padded">
        <div class="wrapper">
            <div class="container__block -narrow">
                <form method="POST" action="{{ route('admin.actions.update', $action->id) }}">
                    {{ csrf_field()}}
                    {{ method_field('PATCH') }}

                    <div class="form-item">
                        <label class="field-label">Action Name</label>
                        @include('admin.forms.text', ['name' => 'name', 'placeholder' => 'Name your action e.g. Teens for Jeans Photo Upload', 'value' => $action->name])
                    </div>

                    <div class="form-item">
                        <label class="field-label">Post Type</label>
                        @include('admin.forms.select', ['name' => 'post_type', 'options' => $postTypes, 'value' => $action->post_type, 'placeholder' => 'What type of post is this?'])
                    </div>

                    <div class="form-item">
                        <label class="field-label">Action Type</label>
                        @include('admin.forms.select', ['name' => 'action_type', 'options' => $actionTypes, 'value' => $action->action_type, 'placeholder' => 'What type of action is this?'])
                    </div>

                    <div class="form-item">
                        <label class="field-label">Time Commitment</label>
                        @include('admin.forms.select', ['name' => 'time_commitment', 'options' => $timeCommitments, 'value' => $action->time_commitment, 'placeholder' => 'How long will this take?'])
                    </div>

                    <div class="form-item -third">
                        <label class="field-label">CallPower Campaign ID</label>
                        @include('admin.forms.text', ['name' => 'callpower_campaign_id', 'placeholder' => 'e.g. 4 (optional)', 'value' => $action->callpower_campaign_id])
                    </div>

                    <div class="form-item -third">
                        <label class="field-label">Impact Goal (optional)</label>
                        @include('admin.forms.text', ['name' => 'impact_goal', 'placeholder' => 'This total quantity goal will appear on the campaign\'s progress bar.', 'value' => $action->impact_goal])
                    </div>

                    <div class="form-item -third">
                        <label class="field-label">Action Noun</label>
                        @include('admin.forms.text', ['name' => 'noun', 'placeholder' => 'e.g. Jeans', 'value' => $action->noun])
                    </div>

                    <div class="form-item -third">
                        <label class="field-label">Action Verb</label>
                        @include('admin.forms.text', ['name' => 'verb', 'placeholder' => 'e.g. Collected', 'value' => $action->verb])
                    </div>

                    <div class="form-item">
                        <label class="field-label">Action Details</label>

                        @include('admin.forms.option', ['name' => 'reportback', 'label' => 'Reportback', 'value' => $action->reportback])
                        @include('admin.forms.option', ['name' => 'civic_action', 'label' => 'Civic Action', 'value' => $action->civic_action])
                        @include('admin.forms.option', ['name' => 'scholarship_entry', 'label' => 'Scholarship Entry', 'value' => $action->scholarship_entry])

                        <label class="option -checkbox">
                            <input type="checkbox" name="volunteer_credit" {{ old("volunteer_credit", ! empty($action->volunteer_credit)) ? 'checked' : '' }}>
                            <span class="option__indicator"></span>
                            <span>Volunteer Credit <em>(read more about how Volunteer Credits work <a href="https://docs.google.com/document/d/1QG_jC6bKtzp4wSVuQAKPlinM62ALlyl1XQKZyKdB06g/edit#" target="_blank">here</a>)</em></span>
                        </label>

                        @include('admin.forms.option', ['name' => 'anonymous', 'label' => 'Anonymous', 'value' => $action->anonymous])
                        @include('admin.forms.option', ['name' => 'online', 'label' => 'Online Action', 'value' => $action->online])
                        @include('admin.forms.option', ['name' => 'quiz', 'label' => 'Quiz Action', 'value' => $action->quiz])
                        @include('admin.forms.option', ['name' => 'collect_school_id', 'label' => 'Collect School ID', 'value' => $action->collect_school_id])
                    </div>

                    <ul class="form-actions -inline -padded">
                        <li><input type="submit" class="button" value="Update Action"></li>
                        <li><a href="{{ url()->previous() }}" class="button -tertiary">Cancel</a></li>
                    </ul>
                </form>
            </div>
        </div>
    </div>
@stop
