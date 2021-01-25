@extends('layouts.master')

@section('main_content')

    @include('layouts.header', ['header' => 'Campaign IDs'])

    <div class="container -padded">
        <div class="wrapper" data-container="CampaignIdSingle">
            Loading...
        </div>
    </div>

@stop

