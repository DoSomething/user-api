@extends('admin.layouts.importer')

@section('title', 'Upload CSV')

@section('main_content')

<div>
    <form action={{-- route('admin.imports.upload', ['importType' => $importType]) --}} method="post" enctype="multipart/form-data">
        {{ csrf_field() }}

        @if ($importType === \App\Types\ImportType::$rockTheVote)
            <a href="/admin/imports/create?type=rock-the-vote&source=test" class="pull-right">Test Import</a>

            @include('admin.imports.partials.rock-the-vote.create', ['config' => $config])
        @elseif ($importType === \App\Types\ImportType::$emailSubscription)
            @include('admin.imports.partials.email-subscription.create')
        @elseif ($importType === \App\Types\ImportType::$mutePromotions)
            @include('admin.imports.partials.mute-promotions.create')
        @endif

        <div class="form-group">
            <h3>Upload</h3>

            <div class="input-group">
                <label class="input-group-btn">
                    <span class="btn btn-default">
                        Select CSV <input type="file" name="upload-file" style="display: none;" multiple>
                    </span>
                </label>

                <input type="text" class="form-control" readonly>
            </div>
        </div>

        <div>
            <input type="submit" class="btn btn-primary btn-lg" value="Import">
        </div>
    </form>
</div>

<h2>Progress Log</h2>

<div id="messages">
    <!--Messages goes here-->
</div>

<div class="progress">
    <div class="progress-bar" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" style="width: 0%"></div>
</div>

@endsection
