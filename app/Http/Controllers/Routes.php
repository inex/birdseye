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

    private function getProtocolRoutesCount($protocol) {
        if( $routesCount = Cache::get( $this->cacheKey() . 'routes-protocols-' . $protocol . '-count' ) ) {
            $this->cacheUsed = true;
        } else {
            $routesCount = app('Bird')->routesProtocolCount($protocol);
            Cache::put($this->cacheKey() . 'routes-protocols-' . $protocol . '-count', $routesCount, env( 'CACHE_ROUTES', 5 ) );
        }

        if( $routesCount['routes'] === null ) {
            about( 500, 'Could not get route count for protocol ' . $protocol );
        }
        return $routesCount;
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

    private function getTableRoutesCount($table) {
        if( $routesCount = Cache::get( $this->cacheKey() . 'routes-table-' . $table . '-count' ) ) {
            $this->cacheUsed = true;
        } else {
            $routesCount = app('Bird')->routesTableCount($table);
            Cache::put($this->cacheKey() . 'routes-table-' . $table . '-count', $routesCount, env( 'CACHE_ROUTES', 5 ) );
        }

        if( $routesCount['routes'] === null ) {
            about( 500, 'Could not get route count for table ' . $table );
        }
        return $routesCount;
    }

    public function protocolCount($protocol)
    {
        // let's make sure the protocol is valid:
        if( !in_array( $protocol, $this->getSymbols()['protocol'] ) ) {
            abort( 404, "Invalid protocol" );
        }

        return $this->verifyAndSendJSON( 'count', $this->getProtocolRoutesCount($protocol), ['from_cache' => $this->cacheUsed, 'ttl_mins' => env( 'CACHE_ROUTES', 5 ) ] );
    }


    public function protocol($protocol)
    {
        // let's make sure the protocol is valid:
        if( !in_array( $protocol, $this->getSymbols()['protocol'] ) ) {
            abort( 404, "Invalid protocol" );
        }

        // does the number of routes exceed the maximum?
        $count = $this->getProtocolRoutesCount($protocol)['routes'];

        if( $count > env('MAX_ROUTES',1000) ) {
            abort( 403, 'Number of routes exceeds maximum allowed' );
        }

        // reset cache used flag after above query:
        $this->cacheUsed = false;

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

    public function tableCount($table)
    {
        // let's make sure the protocol is valid:
        if( !in_array( $table, $this->getSymbols()['routing table'] ) ) {
            abort( 404, "Invalid table" );
        }

        // does the number of routes exceed the maximum?
        $count = $this->getTableRoutesCount($table)['routes'];

        if( $count > env('MAX_ROUTES',1000) ) {
            abort( 403, 'Number of routes exceeds maximum allowed' );
        }

        // reset cache used flag after above query:
        $this->cacheUsed = false;

        return $this->verifyAndSendJSON( 'count', $this->getTableRoutesCount($table), ['from_cache' => $this->cacheUsed, 'ttl_mins' => env( 'CACHE_ROUTES', 5 ) ] );
    }


    private function getLookupRoutes($net,$table) {
        if( $routes = Cache::get( $this->cacheKey() . 'routes-lookup-' . $net . '-table-' . $table ) ) {
            $this->cacheUsed = true;
        } else {
            $routes = app('Bird')->routesLookup($net,$table);
            Cache::put($this->cacheKey() . 'routes-lookup-' . $net . '-table-' . $table, $routes, env( 'CACHE_ROUTES', 5 ) );
        }
        return $routes;
    }

    public function lookup( $net, $table = 'master' ) {
        $net = urldecode($net);

        // validate net as a IP / network
        if( !( preg_match( "/^([0-9]{1,3}\.){3}[0-9]{1,3}(\/\d{1,2}){0,1}$/", $net)
                || preg_match( "/^[a-f0-9\:]+(\/\d{1,3}){0,1}$/", $net ) ) ) {

            // FIXME: a better v6 checker would be useful
            abort(400,'Bad IP address');
        }

        return $this->verifyAndSendJSON( 'routes', $this->getLookupRoutes($net, $table), ['from_cache' => $this->cacheUsed, 'ttl_mins' => env( 'CACHE_ROUTES', 5 ) ] );
    }

}
