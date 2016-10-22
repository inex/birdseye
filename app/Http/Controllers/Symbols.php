<?php

namespace App\Http\Controllers;

use Cache;

class Symbols extends Controller
{
    private $cacheUsed = false;

    private function getSymbols() {
        if( $symbols = Cache::get( $this->cacheKey() . 'symbols' ) ) {
            $this->cacheUsed = true;
        } else {
            $symbols = app('Bird')->symbols();
            Cache::put($this->cacheKey() . 'symbols', $symbols, 1 );
        }
        return $symbols;
    }

    public function all()
    {
        return $this->verifyAndSendJSON( 'symbols', $this->getSymbols(), ['from_cache' => $this->cacheUsed] );
    }


    public function tables()
    {
        $symbols = $this->getSymbols();

        if( !isset( $symbols['routing table'] ) ) {
            $symbols['routing table'] = [];
        }

        return $this->verifyAndSendJSON( 'symbols', $symbols['routing table'], ['from_cache' => $this->cacheUsed] );
    }

    public function protocols()
    {
        $symbols = $this->getSymbols();

        if( !isset( $symbols['protocol'] ) ) {
            $symbols['protocol'] = [];
        }

        return $this->verifyAndSendJSON( 'symbols', $symbols['protocol'], ['from_cache' => $this->cacheUsed] );
    }

}
