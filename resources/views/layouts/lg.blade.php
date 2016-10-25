<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="icon" href="../../favicon.ico">

    <title>Bird's Eye - Looking Glass</title>

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">

    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs-3.3.6/dt-1.10.12/r-2.1.0/datatables.min.css"/>

    <style>
        /* Custom page footer */
        .footer {
          padding-top: 19px;
          margin-top: 30px;
          color: #777;
          border-top: 1px solid #e5e5e5;
        }
    </style>

  </head>

  <body>

    <nav class="navbar navbar-inverse navbar-fixed-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="#">Bird's Eye :: API
              @if( env('LOOKING_GLASS_ENABLED',false) )
                and Looking Glass
              @endif
          </a>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
          <ul class="nav navbar-nav">
            @if( env('LOOKING_GLASS_ENABLED',false) )
                <li><a href="{{$url}}/lg">Looking Glass</a></li>
            @endif
            <li><a href="{{$url}}/">API Overview</a></li>
            <li><a href="https://github.com/inex/birdseye/">GitHub</a></li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </nav>

    <div class="container">
      <br><br><br>
      @if (env('USE_BIRD_DUMMY',false) )
        <div class="alert alert-warning" role="alert">
            This API is in 'dummy' mode using test data
        </div>
      @endif

      @section('header')
          <div class="starter-template">
            <h1>Bird's Eye Looking Glass - API V{{{ $_ENV['BIRDSEYE_API_VERSION'] }}}</h1>
            <p class="lead">
                Bird {{$status->status->version}}.
                @if( isset( $status->status->router_id ))
                    Router ID: {{$status->status->router_id}}.
                @endif
                Uptime: {{ (new DateTime)->diff( DateTime::createFromFormat( 'Y-m-d\TH:i:sO', $status->status->last_reboot ) )->days }} days.
                Last Reconfigure: {{ DateTime::createFromFormat( 'Y-m-d\TH:i:sO', $status->status->last_reconfig )->format( 'Y-m-d H:i:s' ) }}.
                @if (isset( $content->api->from_cache ) and $content->api->from_cache )
                    <br>
                    <span class="small">
                        <em>Results from cached data. Maximum age: {{ $content->api->ttl_mins }}mins.</em>
                    </span>
                @endif
            </p>
          </div>
      @show

      @yield ('content')


        <footer class="footer">
            <p>
                &copy; 2016 Internet Neutral Exchange Association Limited by Guarantee
                (<a href="https://www.inex.ie/">INEX</a>).
                <a href="https://github.com/inex/birdseye/blob/master/LICENSE.md">MIT License</a>.<br>
                A simple secure PHP micro service to provide some Bird protocol / routing information via a HTTP API as JSON.
                This was the winning project from <a href="https://atlas.ripe.net/hackathon/ixp-tools/">the RIPE IXP Tools Hackaton</a>
                just prior to <a href="https://ripe73.ripe.net/">RIPE73</a> in Madrid, Spain. Find the code on
                <a href="https://github.com/inex/birdseye">GitHub</a>.
            </p>
        </footer>
    </div><!-- /.container -->


    <script src="https://code.jquery.com/jquery-2.2.4.min.js" integrity="sha256-BbhdlvQf/xTY9gja0Dq3HiwQF8LaCRTXxZKRutelT44=" crossorigin="anonymous"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/v/bs-3.3.6/dt-1.10.12/r-2.1.0/datatables.min.js"></script>

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
    </script>

    @yield('scripts')

  </body>
</html>
<html>
