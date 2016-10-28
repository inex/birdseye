@extends('layouts.lg')


@section('content')

<h2>Routing Table for {{ucfirst($source)}} <code>{{$name}}</code></h2>


<table class="table" id="routes">
    <thead>
        <tr>
            <th>Network</th>
            <th>Next Hop</th>
            <th></th>
            <th>Metric</th>
            <th>AS Path</th>
            <th></th>
        </tr>
    </thead>
    <tbody>

@forelse ($content->routes as $r )

    <tr>
        <td>
            <a href="{{$url}}/lg/route/{{urlencode($r->network)}}/table/master"
                    data-toggle="modal" data-target="#route-modal">
                {{$r->network}}
            </a>
        </td>
        <td>
            {{$r->gateway}}
        </td>
        <td>
            @if ( $r->primary )
                <span class="label label-success">P</span>
            @else
                <span class="label label-warning">N</span>
            @endif
        </td>
        <td>{{$r->metric}}</td>
        <td>
            @if( isset($r->bgp->as_path) )
                {{implode(' ', $r->bgp->as_path)}}
            @endif
        </td>
        <td>
            <a class="btn btn-default btn-xs" data-toggle="modal"
                href="{{$url}}/lg/route/{{urlencode($r->network)}}/{{$source}}/{{$name}}"
                data-target="#route-modal">Details</a>
        </td>
    </tr>

@empty

<tr><td colspan="4">No routes found</td></tr>

@endforelse

    </tbody>
</table>

<p>
    <br><br>
    Key: <span class="label label-success">P</span> - Primary / active route. <span class="label label-warning">N</span> - Inactive route.
</p>

<div class="modal fade" id="route-modal" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
    </div>
  </div>
</div>



@endsection




@section('scripts')

    <script type="text/javascript">


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
