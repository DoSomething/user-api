@extends('admin.layouts.importer')

@section('title', 'Email Subscription Imports')

@section('main_content')

<h1>Email Subscriptions</h1>

<hr />

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

        @foreach($imports as $import)
            <tr class="row">
              <td class="col-md-3">
                <a href="/admin/imports/email-subscription/{{ $import->id }}">
                  <strong>{{ $import->created_at }}</strong>
                </a>
              </td>

              <td class="col-md-3">
                {{ $import->import_type }}

                @if ($import->options)
                  @include('admin.imports.partials.import-files.import-options', ['options' => $import->options])
                @endif
              </td>

              <td class="col-md-3">
                {{ $import->import_count }}
              </td>

              <td class="col-md-3">
                {{ $import->user_id ? $import->user_id : 'Console' }}
              </td>
            </tr>
        @endforeach
    </table>

    {{ $imports->links('admin.imports.partials.bootstrap-pagination') }}
</div>

@stop
