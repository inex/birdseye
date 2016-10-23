<html>

<body>

    <h1>Bird's Eye V{{{ env('API_VERSION','0.0.0') }}}</h1>


    @if (env('USE_BIRD_DUMMY',false) )
        <h2>WARNING: In 'dummy' mode using test data</h2>
    @endif

    <h2>Implemented Endpoints</h2>

    <ul>
        <li> <a href="{{{ url('api/status') }}}">{{{ url('api/status') }}}</a> </li>
    </ul>
    <ul>
        <li> <a href="{{{ url('api/protocols/bgp') }}}">{{{ url('api/protocols/bgp') }}}</a> </li>
        <li> {{{ url('api/protocol') }}}/$protocol </li>
    </ul>
    <ul>
        <li> <a href="{{{ url('api/symbols') }}}">{{{ url('api/symbols') }}}</a> </li>
        <li> <a href="{{{ url('api/symbols/tables') }}}">{{{ url('api/symbols/tables') }}}</a> </li>
        <li> <a href="{{{ url('api/symbols/protocols') }}}">{{{ url('api/symbols/protocols') }}}</a> </li>
    </ul>
    <ul>
        <li> {{{ url('api/routes/protocol')}}}/$protocol
            @if (env('USE_BIRD_DUMMY',false) )
                <br>
                E.g. <a href="{{{ url( 'api/routes/protocol/pb_0127_as42227' ) }}}">{{{url( 'api/routes/protocol/pb_0127_as42227' )}}}</a>
            @endif
        </li>
        <li> {{{ url('api/routes/table')}}}/$table
            @if (env('USE_BIRD_DUMMY',false) )
                <br>
                E.g. <a href="{{{ url( 'api/routes/table/t_0119_as50088' ) }}}">{{{url( 'api/routes/table/t_0119_as50088' )}}}</a>
            @endif
        </li>
    </ul>
    <ul>
        <li> {{{ url('api/routes/count/protocol')}}}/$protocol
            @if (env('USE_BIRD_DUMMY',false) )
                <br>
                E.g. <a href="{{{ url( 'api/routes/count/protocol/pb_0127_as42227' ) }}}">{{{url( 'api/routes/count/protocol/pb_0127_as42227' )}}}</a>
            @endif
        </li>
        <li> {{{ url('api/routes/count/table')}}}/$table
            @if (env('USE_BIRD_DUMMY',false) )
                <br>
                E.g. <a href="{{{ url( 'api/routes/count/table/t_0119_as50088' ) }}}">{{{url( 'api/routes/count/table/t_0119_as50088' )}}}</a>
            @endif
        </li>
    </ul>
</body>
</html>
