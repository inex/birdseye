<html>

<body>

    <h1>Bird's Eye V{{{ env('API_VERSION','0.0.0') }}}</h1>


    @if (env('USE_BIRD_DUMMY',false) )
        <h2>WARNING: In 'dummy' mode using test data</h2>
    @endif

    <h2>Implemented Endpoints</h2>

    <ul>
        <li> <a href="{{{ url('api/status') }}}">{{{ url('api/status') }}}</a> </li>
        <li> <a href="{{{ url('api/protocols/bgp') }}}">{{{ url('api/protocols/bgp') }}}</a> </li>
        <li> {{{ url('api/protocol') }}}/$protocol </li>
    </ul>
</body>
</html>
