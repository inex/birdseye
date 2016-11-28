<?php

namespace App\Http\Controllers;

use Cache;

class Protocols extends Controller
{
    // private function getProtocols() {
    //     if( $protocols = Cache::get( $this->cacheKey() . 'protocols' ) ) {
    //         $this->cacheUsed = true;
    //     } else {
    //         $protocols = app('Bird')->protocols();
    //         Cache::put($this->cacheKey() . 'protocols', $protocols, 1 );
    //     }
    //     return $protocols;
    // }

    private function getProtocolsBgp() {
        if( !$this->cacheDisabled && $protocols = Cache::get( $this->cacheKey() . 'protocols-bgp' ) ) {
            $this->cacheUsed = true;
        } else {
            $protocols = app('Bird')->protocolsBgp();
            Cache::put($this->cacheKey() . 'protocols-bgp', $protocols, env( 'CACHE_PROTOCOLS', 2 ) );
        }
        return $protocols;
    }

    // public function all()
    // {
    //     return $this->verifyAndSendJSON( 'protocols', $this->getProtocols(), ['from_cache' => $this->cacheUsed,'ttl_mins' => env( 'CACHE_PROTOCOLS', 2 )] );
    // }

    public function bgp()
    {
        $protocols = $this->getProtocolsBgp();

        return $this->verifyAndSendJSON( 'protocols', $protocols, ['from_cache' => $this->cacheUsed,'ttl_mins' => env( 'CACHE_PROTOCOLS', 2 ) ] );
    }

    public function protocol($protocol)
    {
        $protocols = $this->getProtocolsBgp();

        if( !isset( $protocols[$protocol] ) ) {
            abort( 404, "Protocol not found" );
        }

        return $this->verifyAndSendJSON( 'protocol', $protocols[$protocol], ['from_cache' => $this->cacheUsed,'ttl_mins' => env( 'CACHE_PROTOCOLS', 2 )] );
    }

}
