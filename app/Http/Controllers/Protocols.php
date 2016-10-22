<?php

namespace App\Http\Controllers;

class Protocols extends Controller
{
    public function all()
    {
        $protocols = app('Bird')->protocols();

        return $this->verifyAndSendJSON( $protocols );
    }

    public function bgp()
    {
        $protocols = app('Bird')->protocolsBgp();

        return $this->verifyAndSendJSON( 'protocols', $protocols );
    }

    public function protocol($protocol)
    {
        $protocols = app('Bird')->protocolsBgp();

        if( !isset( $protocols[$protocol] ) ) {
            abort( 404, "Protocol not found" );
        }

        return $this->verifyAndSendJSON( 'protocol', $protocols[$protocol] );
    }

}
