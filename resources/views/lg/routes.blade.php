@extends('layouts.lg')


@section('content')

<table class="table" id="routes">
    <thead>
        <tr>
            <th>Network</th>
            <th>Next Hop</th>
            <th>Metric</th>
            <th>AS Path</th>
        </tr>
    </thead>
    <tbody>

@forelse ($routes->routes as $r )

    <tr>
        <td>{{$r->network}}</td>
        <td>{{$r->gateway}}</td>
        <td>{{$r->metric}}</td>
        <td>
            @if( isset($r->bgp->as_path) )
                {{implode(' ', $r->bgp->as_path)}}
            @endif
        </td>
    </tr>

@empty

<tr><td colspan="4">No routes found</td></tr>

@endforelse

    </tbody>
</table>

@endsection

@section('scripts')

    <script type="text/javascript">

        jQuery.extend( jQuery.fn.dataTableExt.oSort, {
            "ip-address-pre": function ( a ) {
                var i, item;
                var m = a.split("."),
                    n = a.split(":"),
                    x = "",
                    xa = "";

                if (m.length == 4) {
                    // IPV4
                    for(i = 0; i < m.length; i++) {
                        item = m[i];

                        if(item.length == 1) {
                            x += "00" + item;
                        }
                        else if(item.length == 2) {
                            x += "0" + item;
                        }
                        else {
                            x += item;
                        }
                    }
                }
                else if (n.length > 0) {
                    // IPV6
                    var count = 0;
                    for(i = 0; i < n.length; i++) {
                        item = n[i];

                        if (i > 0) {
                            xa += ":";
                        }

                        if(item.length === 0) {
                            count += 0;
                        }
                        else if(item.length == 1) {
                            xa += "000" + item;
                            count += 4;
                        }
                        else if(item.length == 2) {
                            xa += "00" + item;
                            count += 4;
                        }
                        else if(item.length == 3) {
                            xa += "0" + item;
                            count += 4;
                        }
                        else {
                            xa += item;
                            count += 4;
                        }
                    }

                    // Padding the ::
                    n = xa.split(":");
                    var paddDone = 0;

                    for (i = 0; i < n.length; i++) {
                        item = n[i];

                        if (item.length === 0 && paddDone === 0) {
                            for (var padding = 0 ; padding < (32-count) ; padding++) {
                                x += "0";
                                paddDone = 1;
                            }
                        }
                        else {
                            x += item;
                        }
                    }
                }

                return x;
            },

            "ip-address-asc": function ( a, b ) {
                return ((a < b) ? -1 : ((a > b) ? 1 : 0));
            },

            "ip-address-desc": function ( a, b ) {
                return ((a < b) ? 1 : ((a > b) ? -1 : 0));
            }
        });

        $('#routes')
            .removeClass( 'display' )
            .addClass('table');

        $(document).ready(function() {
            $('#routes').DataTable({
                paging: false,
                order: [[ 0, "asc" ]],
                columnDefs: [
                    { type: 'ip-address', targets: 0 },
                    { type: 'ip-address', targets: 0 },
                    { type: 'int', targets: 0 },
                    { type: 'string', targets: 0 }
                ]
            });
        });

    </script>

@endsection
