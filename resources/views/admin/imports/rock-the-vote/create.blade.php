@extends('admin.layouts.importer')

@section('title', 'Rock The Vote Imports')

@section('main_content')

<h1>Rock The Vote</h1>

<div style="display: block; margin-bottom: 15px; margin-top: 15px; overflow: auto;">

    <a href="/admin/imports/rock-the-vote/test" class="pull-left">Test Import</a>

    <a href="/admin/imports/rock-the-vote" class="pull-right">View Imported Reports List</a>
</div>

<hr />

<form action={{ route('admin.imports.rock-the-vote.store') }} method="post" enctype="multipart/form-data">
    {{ csrf_field() }}

    <div>
        <p>
            Use this form to create and import a new Rock The Vote report.
        <p>

        <div class="form-group row">
            <label for="since" class="col-sm-3 col-form-label" required>Since</label>

            <div class="col-sm-9">
              <input type="text" class="form-control" name="since">

              <small>e.g. 2020-02-28 12:00:00</small>
            </div>
        </div>

        <div class="form-group row">
            <label for="before" class="col-sm-3 col-form-label" required>Before</label>

            <div class="col-sm-9">
              <input type="text" class="form-control" name="before">

              <small>e.g. 2020-02-28 13:00:00</small>
            </div>
        </div>

        <div>
            <input type="submit" class="btn btn-primary btn-lg" value="Create">
        </div>
    </div>

</form>

<hr />

@include('admin.imports.rock-the-vote.configuration')

@endsection
