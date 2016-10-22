<?php

namespace App\Http\Controllers;

use Cache;

class Symbols extends Controller
{
    public function all()
    {
        return $this->verifyAndSendJSON( 'symbols', $this->getSymbols(), ['from_cache' => $this->cacheUsed,'ttl_mins' => env( 'CACHE_SHOW_SYMBOLS', 5 )] );
    }


    public function tables()
    {
        $symbols = $this->getSymbols();

        if( !isset( $symbols['routing table'] ) ) {
            $symbols['routing table'] = [];
        }

        return $this->verifyAndSendJSON( 'symbols', $symbols['routing table'], ['from_cache' => $this->cacheUsed,'ttl_mins' => env( 'CACHE_SHOW_SYMBOLS', 5 )] );
    }

    public function protocols()
    {
        $symbols = $this->getSymbols();

        if( !isset( $symbols['protocol'] ) ) {
            $symbols['protocol'] = [];
        }

        return $this->verifyAndSendJSON( 'symbols', $symbols['protocol'], ['from_cache' => $this->cacheUsed,'ttl_mins' => env( 'CACHE_SHOW_SYMBOLS', 5 )] );
    }

}
