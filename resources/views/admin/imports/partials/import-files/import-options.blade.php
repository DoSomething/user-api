<ul style="padding: 0;">
  @foreach (json_decode($options) as $key => $value)
    @if ($key === 'report_id')
      <li>
        <a href="/rock-the-vote-reports/{{$value}}">
          <strong>#{{$value}}</strong>
        </a>
      </li>
    @else
      <li>{{$key}}: <strong>{{$value}}</strong></li>
    @endif
  @endforeach
</ul>
