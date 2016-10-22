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

        return $this->verifyAndSendJSON( $protocols );
    }
}
