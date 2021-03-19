@extends('admin.layouts.main')

@section('title', 'Users')

@section('header_content')
    @include('admin.layouts.header', ['subtitle' => 'View & edit member profiles.'])
@endsection

@section('main_content')
    <div class="container -padded">
        <div class="wrapper">
            <div class="container__block">
                <h1>All Users</h1>

                <form action="/admin/users" method="GET">
                    {{ csrf_field() }}
                    <div class="form-actions -inline">
                        <li><input class="text-field -search" placeholder="Find user by email, mobile, ID…" style="min-width: 400px" name="search" type="text"></li>
                        <li><input class="button -secondary" type="submit" value="Search"></li>
                    </div>
                </form>
            </div>

            <div class="container__block">
                @if ($users)
                    <table id="user-table" class="table">
                        <thead>
                            <tr class="row table-header">
                                <th class="table-cell">Name</th>

                                <th class="table-cell">Contact Methods</th>

                                <th class="table-cell">Last Visited</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($users as $user)
                                <tr class="table-row">
                                    <td class="table-cell"><a href="{{ route('admin.users.show', [$user->id]) }}">{{ $user->display_name }}</a></td>

                                    <td class="table-cell">
                                        <code>{{ $user->email_preview }}</code>
                                        @if ($user->email_preview && $user->mobile_preview)
                                            <span class="footnote"> and </span>
                                        @endif
                                        <code>{{ $user->mobile_preview }}</code>
                                    </td>

                                    <td class="table-cell footnote">
                                        {{ $user->last_accessed_at ? $user->last_accessed_at->diffForHumans() : 'N/A' }}
                                    </td>
                                </tr>
                            @empty
                            @endforelse
                        </tbody>
                    </table>

                    {{ $users->links() }}
                @endif
            </div>
        </div>
    </div>
@stop
