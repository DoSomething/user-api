@extends('layouts.admin')

@section('title', 'Redirects')
@section('subtitle', 'Create & manage URL redirects.')

@section('main_content')
    <div class="container -padded">
        <div class="wrapper">
            <div class="container__block -narrow">
                <div class="gallery__heading"><h1>All Redirects</h1></div>
                <p>These are the URL redirects registered in Fastly.</p>
            </div>
            <div class="container__block -narrow">
                <a class="button -secondary" href="{{ route('redirects.create') }}">New redirect</a>
            </div>
            <div class="container__block">
                <table class="table">
                    <thead>
                        <tr class="row table-header">
                            <th class="table-cell">Path</th>
                            <th class="table-cell">Target</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($redirects as $redirect)
                            <tr class="table-row">
                                <td class="table-cell break-all" title="{{ $redirect->path }}"><a href="{{ route('redirects.show', [$redirect->id]) }}">{{ Illuminate\Support\Str::limit($redirect->path, 40) }}</a></td>
                                <td class="table-cell break-all" title="{{ $redirect->target }}">{{ Illuminate\Support\Str::limit($redirect->target, 60) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@stop
