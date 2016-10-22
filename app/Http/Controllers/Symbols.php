<?php

namespace App\Http\Controllers;

class Symbols extends Controller
{
    public function all()
    {
        $symbols = app('Bird')->symbols();

        return $this->verifyAndSendJSON( 'symbols', $symbols );
    }

    public function tables()
    {
        $symbols = app('Bird')->symbols();

        if( !isset( $symbols['routing table'] ) ) {
            $symbols['routing table'] = [];
        }

        return $this->verifyAndSendJSON( 'symbols', $symbols['routing table'] );
    }

    public function protocols()
    {
        $symbols = app('Bird')->symbols();

        if( !isset( $symbols['protocol'] ) ) {
            $symbols['protocol'] = [];
        }

        return $this->verifyAndSendJSON( 'symbols', $symbols['protocol'] );
    }

}
