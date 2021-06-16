@extends('admin.layouts.importer')

@section('title', 'Email Subscription Import details')

@section('main_content')

    <h1>Email Subscription Import File ID: <code>{{ $importFile->id }}</code></h1>

    <hr />

    <h3>Imported on {{ $importFile->created_at->setTimezone('America/New_York')->toDayDateTimeString() }}  </h3>

    @if ($import->options)
        {{-- @include('admin.imports.partials.import-files.import-options', ['options' => $import->options]) --}}
    @endif

    <p>This file had a total of {{$importFile->row_count}} rows: <strong>{{$importFile->import_count}} imported, {{$importFile->skip_count}} skipped</strong>.</p>

@endsection
