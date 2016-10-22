<?php

namespace App\Http\Controllers;

use Cache;

class Routes extends Controller
{
    private function getProtocolRoutes($protocol) {
        if( $routes = Cache::get( $this->cacheKey() . 'routes-protocols-' . $protocol ) ) {
            $this->cacheUsed = true;
        } else {
            $routes = app('Bird')->routesProtocol($protocol);
            Cache::put($this->cacheKey() . 'routes-protocols-' . $protocol, $routes, env( 'CACHE_ROUTES', 5 ) );
        }
        return $routes;
    }

    private function getTableRoutes($table) {
        if( $routes = Cache::get( $this->cacheKey() . 'routes-table-' . $table ) ) {
            $this->cacheUsed = true;
        } else {
            $routes = app('Bird')->routesTable($table);
            Cache::put($this->cacheKey() . 'routes-table-' . $table, $routes, env( 'CACHE_ROUTES', 5 ) );
        }
        return $routes;
    }


    public function protocol($protocol)
    {
        // let's make sure the protocol is valid:
        if( !in_array( $protocol, $this->getSymbols()['protocol'] ) ) {
            abort( 404, "Invalid protocol" );
        }

        return $this->verifyAndSendJSON( 'routes', $this->getProtocolRoutes($protocol), ['from_cache' => $this->cacheUsed, 'ttl_mins' => env( 'CACHE_ROUTES', 5 ) ] );
    }

    public function table($table)
    {
        // let's make sure the protocol is valid:
        if( !in_array( $table, $this->getSymbols()['routing table'] ) ) {
            abort( 404, "Invalid table" );
        }

        return $this->verifyAndSendJSON( 'routes', $this->getTableRoutes($table), ['from_cache' => $this->cacheUsed, 'ttl_mins' => env( 'CACHE_ROUTES', 5 ) ] );
    }

}
