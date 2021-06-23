@extends('admin.layouts.importer')

@section('title', 'Rock The Vote Imports Test')

@section('main_content')

<h1>Rock The Vote Imports Test</h1>

<hr />

<p>Use this form to test importing a <code>rock-the-vote</code> record.<p>

@if (config('import.import_test_form_enabled') == 'true')
    <form action="{{ route('admin.imports.rock-the-vote-test.store') }}" method="post" enctype="multipart/form-data">
        {{ csrf_field() }}

        <h3>User</h3>

        <div class="form-group row">
            <label for="first_name" class="col-sm-3 col-form-label">First Name</label>

            <div class="col-sm-3">
                <input type="text" name="first_name" id="first_name" class="form-control" value="{{ $data['first_name']}} " />
            </div>

            <label for="last_name" class="col-sm-3 col-form-label">Last Name</label>

            <div class="col-sm-3">
                <input type="text" name="last_name" id="last_name" class="form-control" value="{{ $data['last_name']}} " />
            </div>
        </div>

        <div class="form-group row">
            <label for="addr_street1" class="col-sm-3 col-form-label">Home address</label>

            <div class="col-sm-3">
                <input type="text" name="addr_street1" id="addr_street1" class="form-control" value="{{ $data['addr_street1']}} " />
            </div>

            <label for="addr_street2" class="col-sm-3 col-form-label">Home unit</label>
            <div class="col-sm-3">
                <input type="text" name="addr_street2" id="addr_street2" class="form-control" value="{{ $data['addr_street2']}} " />
            </div>
        </div>

        <div class="form-group row">
            <label for="addr_city" class="col-sm-3 col-form-label">Home city</label>

            <div class="col-sm-3">
                <input type="text" name="addr_city" id="addr_city" class="form-control" value="{{ $data['addr_city']}} " />
            </div>

            <label for="addr_zip" class="col-sm-3 col-form-label">Home zip code</label>

            <div class="col-sm-3">
                <input type="text" name="addr_zip" id="addr_zip" class="form-control" value="{{ $data['addr_zip']}} " />
            </div>
        </div>

        <div class="form-group row">
            <label for="email" class="col-sm-3 col-form-label" required>Email</label>

            <div class="col-sm-3">
                <input type="text" name="email" id="email" class="form-control" value="{{ $data['email']}} " />
            </div>

            <label for="phone" class="col-sm-3 col-form-label">Phone</label>

            <div class="col-sm-3">
                <input type="text" name="phone" id="phone" class="form-control" value="{{ $data['phone']}} " />
            </div>
        </div>

        <div class="form-group row">
            <label for="email_opt_in" class="col-sm-3 col-form-label" required>Email Opt-in</label>

            <div class="col-sm-3">
                <input type="checkbox" id="email_opt_in" name="email_opt_in" value="Yes">
            </div>

            <label for="sms_opt_in" class="col-sm-3 col-form-label" required>SMS Opt-in</label>

            <div class="col-sm-3">
                <input type="checkbox" id="sms_opt_in" name="sms_opt_in" value="Yes">
            </div>
        </div>

        <h3>Voter Registration</h3>

        <div class="form-group row">
            <label for="tracking_source" class="col-sm-3 col-form-label" required>Tracking Source</label>

            <div class="col-sm-9">
                <input type="text" name="tracking_source" id="tracking_source" class="form-control" value="{{ $data['tracking_source']}} " />

                <small class="form-text text-muted">The `r` query string value sent, e.g. <code>vote.dosomething.org?r=user:5e9a3c0c9454f2503d3f36d2,source=web,source_details=puppetSlothArchive</code>. See <a href="https://github.com/DoSomething/chompy/blob/master/docs/imports/rock-the-vote.md#online-drives">docs</a>.</small>
            </div>
        </div>

        <div class="form-group row">
            <label for="started_registration" class="col-sm-3 col-form-label" required>Started registration</label>

            <div class="col-sm-9">
                <input type="text" name="started_registration" id="started_registration" class="form-control" value="{{ $data['started_registration']}} " />
            </div>
        </div>

        <div class="form-group row">
            <label for="status" class="col-sm-3 col-form-label" required>Status</label>

            <div class="col-sm-9">
                <div class="select">
                    <select class="form-control" name="status">
                        <option value="">--</option>
                        <option value="Rejected">Rejected</option>
                        <option value="Under 18">Under 18</option>
                        <option value="Step 1" selected="selected">Step 1</option>
                        <option value="Step 2">Step 2</option>
                        <option value="Step 3">Step 3</option>
                        <option value="Step 4">Step 4</option>
                        <option value="Complete">Complete</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-group row">
            <label for="finish_with_state" class="col-sm-3 col-form-label" required>Finish with State</label>

            <div class="col-sm-9">
                <input type="checkbox" id="finish_with_state" name="finish_with_state" value="Yes">
            </div>
        </div>

        <div class="form-group row">
            <label for="pre_registered" class="col-sm-3 col-form-label" required>Pre-Registered</label>

            <div class="col-sm-9">
                <input type="checkbox" id="pre_registered" name="pre_registered" value="Yes">
            </div>
        </div>

        <div>
            <input type="submit" class="btn btn-primary btn-lg" value="Submit">
        </div>

        <hr />

        @include('admin.imports.rock-the-vote.configuration')
    </form>
@else
    <p>This feature is currently disabled.</p>
@endif

@endsection

