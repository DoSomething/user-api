@extends('layouts.admin')

@section('title', 'Redirects')
@section('subtitle', 'Create & manage URL redirects.')

@section('main_content')

    <div class="container -padded">
        <div class="wrapper">
            <div class="container__block -narrow">
                <h1><code>{{ $redirect->path }}</code></h1>
            </div>

            <div class="container__block -half">
                <label class="field-label">Path:</label>
                <code class="break-all"><em>{{ config('services.fastly.frontend_url') }}</em>{{ $redirect->path }}</code><br><br>
                <label class="field-label">Target:</label>
                <code class="break-all">{{ $redirect->target }}</code><br><br>
                <label class="field-label">Redirect Status:</label>
                <code>302 (Found)</code><br><br>
            </div>

            <div class="container__block -half">
                <div class="danger-zone">
                    <h4 class="danger-zone__heading">Danger Zone&#8482;</h4>
                    <div class="danger-zone__block">
                        <div class="form-item">
                            <label for="role" class="field-label">Delete Redirect</label>
                            <p class="footnote">This will
                                <strong>delete</strong> this redirect path and it
                                will no longer be available to users or search
                                engines. The target will not be modified.
                        </div>

                        <a class="button -secondary -danger"
                            href="{{ route('admin.redirects.destroy', $redirect->id) }}"
                            data-method="DELETE" data-confirm="Are you sure you want to delete this redirect?">Delete Redirect</a>
                    </div>
                </div>
            </div>

            <div class="container__block -narrow">
                <a class="secondary" href="{{ route('admin.redirects.edit', [ $redirect->id ]) }}">Edit this redirect</a>
                <p class="footnote">
                    Last updated: {{ $redirect->updated_at->format('F d, Y g:ia') }}<br />
                    Created: {{ $redirect->created_at->format('F d, Y g:ia') }}
                </p>
            </div>
        </div>
    </div>
@stop
