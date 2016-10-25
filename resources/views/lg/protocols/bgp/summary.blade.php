@extends('layouts.lg')


@section('content')

<h2>BGP Protocol Summary</h2>

<table class="table" id="bgpsummary">
    <thead>
        <tr>
            <th>Neighbor</th>
            <th>ASN</th>
            <th>Table</th>
            <th>PfxLimit</th>
            <th>State/PfxRcd</th>
            <th>PfxExp</th>
        </tr>
    </thead>
    <tbody>

@forelse ($content->protocols as $name => $p )

    <tr @if( $p->state != 'up' ) class="warning" @endif>
        <td>{{$p->neighbor_address}}</td>
        <td>{{$p->neighbor_as}}</td>
        <td>
            <a href="{{$url}}/lg/routes/table/{{$p->table}}">
                {{$p->table}}
            </a>
        </td>
        <td>
            @if ( isset($p->import_limit) and isset( $p->route_limit_at ) and $p->import_limit )
                <span
                    @if ( ( (float)$p->route_limit_at / $p->import_limit ) >= .9 )
                        class="label label-danger"
                    @elseif ( ( (float)$p->route_limit_at / $p->import_limit ) >= .8 )
                        class="label label-warning"
                    @endif
                >
                    {{$p->route_limit_at}}/{{$p->import_limit}}
                </span>
            @endif
        </td>
        <td>
            @if( $p->state != 'up' )
                {{{$p->bgp_state}}}</a>
            @else
                <a href="{{$url}}/lg/routes/protocol/{{$name}}">{{$p->routes->imported}}</a>
            @endif
        </td>
        <td>
            @if( $p->state == 'up' )
                <a href="{{$url}}/lg/routes/table/{{$p->table}}">{{$p->routes->exported}}</a>
            @endif
        </td>
    </tr>

@empty

<tr><td colspan="6">No BGP sessions found</td></tr>

@endforelse

    </tbody>
</table>

@endsection

@section('scripts')

    <script type="text/javascript">
        $('#bgpsummary')
            .removeClass( 'display' )
            .addClass('table');

        $(document).ready(function() {
            $('#bgpsummary').DataTable({
                paging: false,
                order: [[ 1, "asc" ]],
                columnDefs: [
                    { type: 'ip-address', targets: 0 },
                    { type: 'int', targets: 0 },
                    { type: 'string', targets: 0 },
                    { type: 'string', targets: 0 },
                    { type: 'int', targets: 0 },
                    { type: 'int', targets: 0 }
                ]
            });
        });

    </script>

@endsection
