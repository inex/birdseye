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

    <title>Bird's Eye</title>

    <!-- Latest compiled and minified CSS -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">

    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
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
          <a class="navbar-brand" href="#">Bird's Eye</a>
        </div>
        <div id="navbar" class="collapse navbar-collapse">
          <ul class="nav navbar-nav">
            <!-- <li class="active"><a href="#">Home</a></li> -->
            <li><a href="https://github.com/inex/birdseye">GitHub</a></li>
          </ul>
        </div><!--/.nav-collapse -->
      </div>
    </nav>

    <div class="container">
      <br><br><br><br><br><br>
      <div class="starter-template">
        <h1>Bird's Eye V{{{ $_ENV['BIRDSEYE_API_VERSION'] }}}</h1>
        <p class="lead">
            An API to Bird for querying BGP protocol details.
        </p>
      </div>

      @if (env('USE_BIRD_DUMMY',false) )
        <div class="alert alert-warning" role="alert">
            This API is in 'dummy' mode using test data
        </div>
      @endif

      <h2>Implemented Endpoints</h2>

      <h3>Status</h3>

      <ul>
          <li> <a href="{{{ $url }}}/api/status">{{{ $url }}}/api/status</a> </li>
      </ul>

      <h3>BGP Protocol Detail</h3>

      <ul>
          <li> <a href="{{{ $url }}}/api/protocols/bgp">{{{ $url }}}/api/protocols/bgp</a> </li>
          <li> {{{ $url }}}/api/protocol/$protocol </li>
      </ul>

      <h3>Intrnal Symbols</h3>

      <em>
          Mostly used internally to validate passed parameters such as protocol and table names.
      </em>

      <ul>
          <li> <a href="{{{ $url }}}/api/symbols">{{{ $url }}}/api/symbols</a> </li>
          <li> <a href="{{{ $url }}}/api/symbols/tables">{{{ $url }}}/api/symbols/tables</a> </li>
          <li> <a href="{{{ $url }}}/api/symbols/protocols">{{{ $url }}}/api/symbols/protocols</a> </li>
      </ul>

      <h3>Routes</h3>

      <ul>
          <li> {{{ $url }}}/api/routes/protocol/$protocol
              @if (env('USE_BIRD_DUMMY',false) )
                  <br>
                  E.g. <a href="{{{ $url }}}/api/routes/protocol/pb_0127_as42227">{{{$url}}}/api/routes/protocol/pb_0127_as42227</a>
              @endif
          </li>
          <li> {{{ $url }}}/api/routes/table/$table
              @if (env('USE_BIRD_DUMMY',false) )
                  <br>
                  E.g. <a href="{{{ $url }}}/api/routes/table/t_0119_as50088">{{{$url}}}/api/routes/table/t_0119_as50088</a>
              @endif
          </li>
      </ul>

      <h3>Routes</h3>

      <p>
          This is an API call to get details for a given ip/prefix in a given table (or <code>master</code> by default) / protocol. Valid example
          ip/prefixs are:
      </p>

      <ul>
          <li> <code>192.0.2.5</code> </li>
          <li> <code>192.0.2.0/24</code> <b>**Must be URL encoded!**</b></li>
          <li> <code>2001:db8::67</code> <b>**Must be URL encoded!**</b></li>
          <li> <code>2001:db8::/64</code> <b>**Must be URL encoded!**</b></li>
      </ul>

      <p>Endpoints:</p>

      <ul>
          <li> {{{ $url }}}/api/route/$net
              @if (env('USE_BIRD_DUMMY',false) )
                  <br>
                  E.g. <a href="{{{ $url }}}/api/route/net/1.2.3.4">{{{ $url }}}/api/route/net/1.2.3.4</a>
              @endif
          </li>
          <li> {{{ $url }}}/api/route/$net/table/$table
              @if (env('USE_BIRD_DUMMY',false) )
                  <br>
                  E.g. <a href="{{{ $url }}}/api/route/net/1.2.3.4/table/master">{{{$url}}}/api/route/net/1.2.3.4/table/master</a>
              @endif
          </li>
          <li> {{{ $url }}}/api/route/$net/protocol/$protocol
              @if (env('USE_BIRD_DUMMY',false) )
                  <br>
                  E.g. <a href="{{{ $url }}}/api/route/net/1.2.3.4/protocol/master">{{{$url}}}/api/route/net/1.2.3.4/table/master</a>
              @endif
          </li>
      </ul>

      <h3>Route Counts</h3>

      <em>
          Used internally to check is the number of routes in a table / protocol exceed the limit of routes we should return.
      </em>
      <ul>
          <li> {{{ $url }}}/api/routes/count/protocol/$protocol
              @if (env('USE_BIRD_DUMMY',false) )
                  <br>
                  E.g. <a href="{{{ $url }}}/api/routes/count/protocol/pb_0127_as42227">{{{$url}}}/api/routes/count/protocol/pb_0127_as42227</a>
              @endif
          </li>
          <li> {{{ $url }}}/api/routes/count/table/$table
              @if (env('USE_BIRD_DUMMY',false) )
                  <br>
                  E.g. <a href="{{{ $url }}}/api/routes/count/table/t_0119_as50088">{{{$url}}}/api/routes/count/table/t_0119_as50088</a>
              @endif
          </li>
      </ul>

    </div><!-- /.container -->


  </body>
</html>
<html>
