@extends('admin.layouts.importer')

@section('title', 'RTV Import details')

@section('main_content')

<div>
    <h1>
        {{$import->created_at}}
    </h1>
    <p>
        <strong>{{$import->import_type}}</strong>
    </p>
    @if ($import->options)
        {{-- @include('admin.imports.partials.import-files.import-options', ['options' => $import->options]) --}}
    @endif
    <p>
        This file had a total of {{$import->row_count}} rows: <strong>{{$import->import_count}} imported, {{$import->skip_count}} skipped</strong>.
    </p>

    @if ($import->import_type === \App\Types\ImportType::$mutePromotions)
        {{-- @include('admin.imports.partials.mute-promotions.logs', ['rows' => $rows, 'user_id' => null]) --}}
    @elseif ($import->import_type === \App\Types\ImportType::$rockTheVote)
        {{-- @include('admin.imports.partials.rock-the-vote.logs', ['rows' => $rows, 'user_id' => null]) --}}
    @endif
</div>

@stop
