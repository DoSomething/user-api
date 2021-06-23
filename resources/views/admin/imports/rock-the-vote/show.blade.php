@extends('admin.layouts.importer')

@section('title', 'Rock The Vote Import details')

@section('main_content')

    <h1>Rock The Vote Import File ID: <code>{{ $importFile->id }}</code></h1>

    <hr />

    <h3>Imported on {{ $importFile->created_at->setTimezone('America/New_York')->toDayDateTimeString() }}  </h3>

    {{-- @if ($import->options)
        @include('admin.imports.partials.import-files.import-options', ['options' => $import->options])
    @endif --}}

    <p>This file had a total of {{$importFile->row_count}} rows: <strong>{{$importFile->import_count}} imported, {{$importFile->skip_count}} skipped</strong>.</p>

    <table class="table">
        <thead>
            <tr class="row">
                <th class="col-md-2">Started Registration</th>

                <th class="col-md-2">User</th>

                <th class="col-md-2">Import File ID</th>

                <th class="col-md-1">Status</th>

                <th class="col-md-4">Tracking Source</th>

                <th class="col-md-1">Finish With State</th>

                <th class="col-md-1">Pre-Registered</th>

                <th class="col-md-1">Contains Phone</th>
            </tr>
        </thead>

        @foreach($importedItems as $importedItem)
            <tr class="row">
                <td class="col-md-2">
                    {{ $importedItem->started_registration }}
                </td>

                <td class="col-md-2">
                    {{ $importedItem->user_id }}
                </td>

                <td class="col-md-2">
                    {{ $importedItem->import_file_id }}
                </td>

                <td class="col-md-1">
                    {{ $importedItem->status }}
                </td>

                <td class="col-md-4">
                    <ul>
                        @foreach(explode(',', $importedItem->tracking_source) as $attribute)
                            <li>{{ $attribute }}</li>
                        @endforeach
                    </ul>
                </td>
                <td class="col-md-1">
                    {{ $importedItem->finish_with_state }}
                </td>
                <td class="col-md-1">
                    {{ $importedItem->pre_registered }}
                </td>
                <td class="col-md-1">
                    {{ $importedItem->contains_phone ? 'Yes' : 'No' }}
                </td>
            </tr>
        @endforeach
    </table>

    {{ $importedItems->links() }}
@endsection
