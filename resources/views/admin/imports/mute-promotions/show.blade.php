@extends('admin.layouts.importer')

@section('title', 'Mute Promotion Import details')

@section('main_content')

    <h1>Mute Promotion Import ID: <code>{{ $importFile->id }}</code></h1>

    <hr />

    <h3>Imported on {{ $importFile->created_at->setTimezone('America/New_York')->toDayDateTimeString() }}  </h3>

    {{-- @if ($import->options)
        @include('admin.imports.partials.import-files.import-options', ['options' => $import->options])
    @endif --}}

    <p>This file had a total of {{$importFile->row_count}} rows: <strong>{{$importFile->import_count}} imported, {{$importFile->skip_count}} skipped</strong>.</p>

    @if($importedItems->count())
        <table class="table">
            <thead>
                <tr class="row">
                    <th>Import File ID</th>
                    <th>User</th>
                </tr>
            </thead>

            @foreach($importedItems as $importedItem)
                <tr class="row">
                    <td><code>{{ $importedItem->import_file_id }}</code></td>

                    <td>$importedItem->user_id</td>
                </tr>
            @endforeach
        </table>

        {{ $importedItems->links('admin.imports.partials.bootstrap-pagination') }}
    @endif
@endsection
