@extends('admin.layouts.importer')

@section('title', 'Mute Promotions Imports')

@section('main_content')

<h1>Mute Promotions</h1>

<div style="display: block; margin-bottom: 15px; margin-top: 15px; overflow: auto;">
    <a href="/admin/imports/mute-promotions" class="pull-right">View Imports List</a>
</div>


<hr />

<form action={{ route('admin.imports.mute-promotions.store') }} method="post" enctype="multipart/form-data">
    {{ csrf_field() }}

    <div class="form-group">
        <p class="lead">
            Updates users in CSV to set a value for the <code>promotions_muted_at</code> field, triggering their Customer.io profile deletion.
        </p>

        <p>Columns:</p>

        <ul>
            <li><code>northstar_id</code> - required</li>
        </ul>
    </div>

    <hr />

    @include('admin.imports.partials.upload-file')

    <div>
        <input type="submit" class="btn btn-primary btn-lg" value="Import">
    </div>

</form>

<hr />

@include('admin.imports.partials.progress')

@endsection

