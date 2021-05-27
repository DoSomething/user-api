<h3>User</h3>
<div class="form-group row">
  <label for="first_name" class="col-sm-3 col-form-label">First Name</label>
  <div class="col-sm-3">
    {!! Form::text('first_name', $data['first_name'], ['class' => 'form-control']) !!}
  </div>
  <label for="last_name" class="col-sm-3 col-form-label">Last Name</label>
  <div class="col-sm-3">
    {!! Form::text('last_name', $data['last_name'], ['class' => 'form-control']) !!}
  </div>
</div>
<div class="form-group row">
  <label for="addr_street1" class="col-sm-3 col-form-label">Home address</label>
  <div class="col-sm-3">
    {!! Form::text('addr_street1', $data['addr_street1'], ['class' => 'form-control']) !!}
  </div>
  <label for="addr_street2" class="col-sm-3 col-form-label">Home unit</label>
  <div class="col-sm-3">
    {!! Form::text('addr_street2', $data['addr_street2'], ['class' => 'form-control']) !!}
  </div>
</div>
<div class="form-group row">
  <label for="addr_city" class="col-sm-3 col-form-label">Home city</label>
  <div class="col-sm-3">
    {!! Form::text('addr_city', $data['addr_city'], ['class' => 'form-control']) !!}
  </div>
  <label for="addr_zip" class="col-sm-3 col-form-label">Home zip code</label>
  <div class="col-sm-3">
    {!! Form::text('addr_zip', $data['addr_zip'], ['class' => 'form-control']) !!}
  </div>
</div>
<div class="form-group row">
  <label for="email" class="col-sm-3 col-form-label" required>Email</label>
  <div class="col-sm-3">
    {!! Form::text('email', $data['email'], ['class' => 'form-control']) !!}
  </div>
  <label for="phone" class="col-sm-3 col-form-label">Phone</label>
  <div class="col-sm-3">
    {!! Form::text('phone', $data['phone'], ['class' => 'form-control']) !!}
  </div>
</div>
<div class="form-group row">
  <label for="email_opt_in" class="col-sm-3 col-form-label" required>Email Opt-in</label>
  <div class="col-sm-3">
    {!! Form::checkbox('email_opt_in', 'Yes', false) !!}
  </div>
  <label for="sms_opt_in" class="col-sm-3 col-form-label" required>SMS Opt-in</label>
  <div class="col-sm-3">
    {!! Form::checkbox('sms_opt_in', 'Yes', false) !!}
  </div>
</div>

<h3>Voter Registration</h3>
<div class="form-group row">
  <label for="tracking_source" class="col-sm-3 col-form-label" required>Tracking Source</label>
  <div class="col-sm-9">
    {!! Form::text('tracking_source', $data['tracking_source'], ['class' => 'form-control']) !!}
      <small class="form-text text-muted">
        The `r` query string value sent, e.g. <code>vote.dosomething.org?r=user:5e9a3c0c9454f2503d3f36d2,source=web,source_details=puppetSlothArchive</code>. See <a href="https://github.com/DoSomething/chompy/blob/master/docs/imports/rock-the-vote.md#online-drives">docs</a>.
      </small>
  </div>
</div>
<div class="form-group row">
  <label for="started_registration" class="col-sm-3 col-form-label" required>Started registration</label>
  <div class="col-sm-9">
    {!! Form::text('started_registration', $data['started_registration'], ['class' => 'form-control']) !!}
  </div>
</div>
<div class="form-group row">
  <label for="status" class="col-sm-3 col-form-label" required>Status</label>
  <div class="col-sm-9">
      <div class="select">
          {!! Form::select('status', [
              'Rejected' => 'Rejected',
              'Under 18' => 'Under 18',
              'Step 1' => 'Step 1',
              'Step 2' => 'Step 2',
              'Step 3' => 'Step 3',
              'Step 4' => 'Step 4',
              'Complete' => 'Complete',
          ], 'Step 1', ['placeholder' => '--', 'class' => 'form-control']) !!}
      </div>
  </div>
</div>
<div class="form-group row">
  <label for="finish_with_state" class="col-sm-3 col-form-label" required>Finish with State</label>
  <div class="col-sm-9">
    {!! Form::checkbox('finish_with_state', 'Yes', false) !!}
  </div>
</div>
<div class="form-group row">
  <label for="pre_registered" class="col-sm-3 col-form-label" required>Pre-Registered</label>
  <div class="col-sm-9">
    {!! Form::checkbox('pre_registered', 'Yes', false) !!}
  </div>
</div>

