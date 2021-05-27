@extends('layouts.master')

@section('title', 'Import details')

@section('main_content')

<div>
    <h1>
        {{$importFile->created_at}}
    </h1>
    <p>
        <strong>{{$importFile->import_type}}</strong>
    </p>
    @if ($importFile->options)
        @include('pages.partials.import-files.import-options', ['options' => $importFile->options])
    @endif
    <p>
        This file had a total of {{$importFile->row_count}} rows: <strong>{{$importFile->import_count}} imported, {{$importFile->skip_count}} skipped</strong>.
    </p>

    @if ($importFile->import_type === \Chompy\ImportType::$mutePromotions)
        @include('pages.partials.mute-promotions.logs', ['rows' => $rows, 'user_id' => null])
    @elseif ($importFile->import_type === \Chompy\ImportType::$rockTheVote)
        @include('pages.partials.rock-the-vote.logs', ['rows' => $rows, 'user_id' => null])
    @endif
</div>

@stop
