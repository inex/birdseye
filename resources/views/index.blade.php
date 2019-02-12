@extends('layouts.lg')


@section('header')
      <div class="starter-template">
        <h1>Bird's Eye V{{{ $_ENV['BIRDSEYE_API_VERSION'] }}}</h1>
        <p class="lead">
            A Simple Secure Micro Service for Querying Bird (JSON API).
        </p>
      </div>
@endsection

  @section('content')

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

      <h3>Internal Symbols</h3>

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
          <li> {{{ $url }}}/api/routes/export/$protocol
              @if (env('USE_BIRD_DUMMY',false) )
                  <br>
                  E.g. <a href="{{{ $url }}}/api/routes/export/pb_0127_as42227">{{{$url}}}/api/routes/export/pb_0127_as42227</a>
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
          <li> {{{ $url }}}/api/route/$net/export/$protocol</li>
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

@endsection
