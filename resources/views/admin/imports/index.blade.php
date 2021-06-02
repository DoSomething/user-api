@extends('admin.layouts.importer')

@section('title', 'Imports')

@section('main_content')

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

        @foreach($data as $key => $importFile)
            <tr class="row">
              <td class="col-md-3">
                <a href="/admin/imports/{{$importFile->id}}">
                  <strong>{{$importFile->created_at}}</strong>
                </a>
              </td>

              <td class="col-md-3">
                {{$importFile->import_type}}

                @if ($importFile->options)
                  @include('admin.imports.partials.import-files.import-options', ['options' => $importFile->options])
                @endif
              </td>

              <td class="col-md-3">
                {{$importFile->import_count}}
              </td>

              <td class="col-md-3">
                {{$importFile->user_id ? $importFile->user_id : 'Console'}}
              </td>
            </tr>
        @endforeach
    </table>

    {{$data->appends(request()->query())->links()}}
</div>

@stop
