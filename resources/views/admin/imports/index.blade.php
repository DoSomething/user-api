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
    <table class="table">
        <thead>
          <tr class="row">
            <th class="col-md-3">Created</th>
            <th class="col-md-3">Import type</th>
            <th class="col-md-3">Import count</th>
            <th class="col-md-3">Created by</th>
          </tr>
        </thead>

        @foreach($importFiles as $importFile)
            <tr class="row">
              <td class="col-md-3">
                <a href="/admin/imports/{{ $importFile->id }}">
                  <strong>{{ $importFile->created_at }}</strong>
                </a>
              </td>

              <td class="col-md-3">
                {{ $importFile->import_type }}

                @if ($importFile->options)
                  @include('admin.imports.partials.import-files.import-options', ['options' => $importFile->options])
                @endif
              </td>

              <td class="col-md-3">
                {{ $importFile->import_count }}
              </td>

              <td class="col-md-3">
                {{ $importFile->user_id ? $importFile->user_id : 'Console' }}
              </td>
            </tr>
        @endforeach
    </table>

    {{ $importFiles->links('admin.imports.partials.bootstrap-pagination') }}
</div>

@stop
