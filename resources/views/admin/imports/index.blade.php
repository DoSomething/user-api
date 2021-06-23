@extends('admin.layouts.importer')

@section('title', 'Imports')

@section('main_content')

<h1>Imports</h1>

<hr />

<div class="dropdown" style="margin-bottom: 15px;">
  <button class="btn btn-default dropdown-toggle" type="button" id="dropdown-filter" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
    Filter
    <span class="caret"></span>
  </button>

  <ul class="dropdown-menu" aria-labelledby="dropdown-filter">
    <li><a href="/admin/imports/email-subscriptions">Email Subscription</a></li>
    <li><a href="/admin/imports/mute-promotions">Mute Promotions</a></li>
    <li><a href="/admin/imports/rock-the-vote">Rock The Vote</a></li>
    <li role="separator" class="divider"></li>
    <li><a href="/admin/imports">Clear Filters</a></li>
  </ul>
</div>

<div>
    <table class="table table-striped">
        <thead>
          <tr class="row">
            <th>Created</th>
            <th>Import type</th>
            <th>Import count</th>
            <th>Created by</th>
          </tr>
        </thead>

        @foreach($importFiles as $importFile)
            <tr class="row">
              <td>
                <a href="/admin/imports/{{$importFile->import_type}}/{{ $importFile->id }}">
                  <strong>{{ $importFile->created_at }}</strong>
                </a>
              </td>

              <td>
                {{ $importFile->import_type }}
              </td>

              <td>
                {{ $importFile->import_count }}
              </td>

              <td>
                {{ $importFile->user_id ? $importFile->user_id : 'Console' }}
              </td>
            </tr>
        @endforeach
    </table>

    {{ $importFiles->links('admin.imports.partials.bootstrap-pagination') }}
</div>

@stop
