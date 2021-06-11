<table class="table">
    <thead>
        <tr class="row">
            <th>{{$user_id ? 'Import File' : 'User'}}</th>
        </tr>
    </thead>

    @foreach($rows as $key => $row)
        <tr class="row">
            <td>
                <a href="{{$user_id ? '/import-files/' . $row->import_file_id : '/users/' . $row->user_id}}">
                    {{$user_id ? $row->import_file_id : $row->user_id}}
                </a>
            </td> 
        </tr>
    @endforeach
</table>

@if(method_exists($rows, 'links'))
    {{$rows->links()}}
@endif
