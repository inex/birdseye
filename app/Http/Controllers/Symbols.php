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

        return $this->verifyAndSendJSON( 'tables', $symbols['routing table'] );
    }

}
