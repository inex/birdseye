<?php

namespace App\Http\Controllers;

use Cache;

class Routes extends Controller
{
    private function getProtocolRoutes($protocol) {
        if( !$this->cacheDisabled && $routes = Cache::get( $this->cacheKey() . 'routes-protocols-' . $protocol ) ) {
            $this->cacheUsed = true;
        } else {
            $routes = app('Bird')->routesProtocol($protocol);
            Cache::put($this->cacheKey() . 'routes-protocols-' . $protocol, $routes, env( 'CACHE_ROUTES', 5 ) );
        }
        return $routes;
    }

    private function getProtocolLargeCommunityWildXYRoutes($protocol,$x,$y) {
        if( !$this->cacheDisabled && $routes = Cache::get( $this->cacheKey() . 'routes-protocols-lcwild-xy-' . $protocol . '-' . $x . '-' . $y ) ) {
            $this->cacheUsed = true;
        } else {
            $routes = app('Bird')->routesProtocolLargeCommunityWildXYRoutes($protocol,$x,$y);
            Cache::put($this->cacheKey() . 'routes-protocols-lcwild-xy-' . $protocol . '-' . $x . '-' . $y, $routes, env( 'CACHE_ROUTES', 5 ) );
        }
        return $routes;
    }

    private function getProtocolRoutesCount($protocol) {
        if( !$this->cacheDisabled && $routesCount = Cache::get( $this->cacheKey() . 'routes-protocols-' . $protocol . '-count' ) ) {
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
        if( !$this->cacheDisabled && $routes = Cache::get( $this->cacheKey() . 'routes-table-' . $table ) ) {
            $this->cacheUsed = true;
        } else {
            $routes = app('Bird')->routesTable($table);
            Cache::put($this->cacheKey() . 'routes-table-' . $table, $routes, env( 'CACHE_ROUTES', 5 ) );
        }
        return $routes;
    }

    private function getTableRoutesCount($table) {
        if( !$this->cacheDisabled && $routesCount = Cache::get( $this->cacheKey() . 'routes-table-' . $table . '-count' ) ) {
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

    private function getExportRoutes($protocol) {
        if( !$this->cacheDisabled && $routes = Cache::get( $this->cacheKey() . 'routes-export-' . $protocol ) ) {
            $this->cacheUsed = true;
        } else {
            $routes = app('Bird')->routesExport($protocol);
            Cache::put($this->cacheKey() . 'routes-export-' . $protocol, $routes, env( 'CACHE_ROUTES', 5 ) );
        }
        return $routes;
    }

    private function getExportRoutesCount($protocol) {
        if( !$this->cacheDisabled && $routesCount = Cache::get( $this->cacheKey() . 'routes-export-' . $protocol . '-count' ) ) {
            $this->cacheUsed = true;
        } else {
            $routesCount = app('Bird')->routesExportCount($protocol);
            Cache::put($this->cacheKey() . 'routes-export-' . $protocol . '-count', $routesCount, env( 'CACHE_ROUTES', 5 ) );
        }

        if( $routesCount['routes'] === null ) {
            about( 500, 'Could not get route count for export to protocol ' . $protocol );
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
            abort( 403, "Number of routes exceeds maximum allowed ({$count}/" . env('MAX_ROUTES',1000) . ")" );
        }

        // reset cache used flag after above count query:
        $this->cacheUsed = false;

        return $this->verifyAndSendJSON( 'routes', $this->getProtocolRoutes($protocol), ['from_cache' => $this->cacheUsed, 'ttl_mins' => env( 'CACHE_ROUTES', 5 ) ] );
    }

    /**
     * API call to return output of:
     *
     * show route all filter { if bgp_large_community ~ [( 2128, 1101, * )] then accept;} protocol pb_as1213_vli222_ipv4
     */
    public function protocolLargeCommunityWildXY( $protocol, $x, $y )
    {
        $x = (int)$x;
        $y = (int)$y;

        // let's make sure the protocol is valid:
        if( !in_array( $protocol, $this->getSymbols()['protocol'] ) || !$x || !$y ) {
            abort( 404, "Invalid protocol" );
        }

        return $this->verifyAndSendJSON( 'routes', $this->getProtocolLargeCommunityWildXYRoutes($protocol,$x,$y), ['from_cache' => $this->cacheUsed, 'ttl_mins' => env( 'CACHE_ROUTES', 5 ) ] );
    }


    public function exportCount($protocol)
    {
        // let's make sure the protocol is valid:
        if( !in_array( $protocol, $this->getSymbols()['protocol'] ) ) {
            abort( 404, "Invalid protocol" );
        }

        return $this->verifyAndSendJSON( 'count', $this->getExportRoutesCount($protocol), ['from_cache' => $this->cacheUsed, 'ttl_mins' => env( 'CACHE_ROUTES', 5 ) ] );
    }


    public function export($protocol)
    {
        // let's make sure the protocol is valid:
        if( !in_array( $protocol, $this->getSymbols()['protocol'] ) ) {
            abort( 404, "Invalid protocol" );
        }

        // does the number of routes exceed the maximum?
        $count = $this->getExportRoutesCount($protocol)['routes'];
        if( $count > env('MAX_ROUTES',1000) ) {
            abort( 403, "Number of routes exceeds maximum allowed ({$count}/" . env('MAX_ROUTES',1000) . ")" );
        }

        // reset cache used flag after above count query:
        $this->cacheUsed = false;

        return $this->verifyAndSendJSON( 'routes', $this->getExportRoutes($protocol), ['from_cache' => $this->cacheUsed, 'ttl_mins' => env( 'CACHE_ROUTES', 5 ) ] );
    }

    public function tableCount($table)
    {
        // let's make sure the protocol is valid:
        if( !in_array( $table, $this->getSymbols()['routing table'] ) ) {
            abort( 404, "Invalid table" );
        }

        return $this->verifyAndSendJSON( 'count', $this->getTableRoutesCount($table), ['from_cache' => $this->cacheUsed, 'ttl_mins' => env( 'CACHE_ROUTES', 5 ) ] );
    }

    public function table($table)
    {
        // let's make sure the protocol is valid:
        if( !in_array( $table, $this->getSymbols()['routing table'] ) ) {
            abort( 404, "Invalid table" );
        }

        // does the number of routes exceed the maximum?
        $count = $this->getTableRoutesCount($table)['routes'];

        if( $count > env('MAX_ROUTES',1000) ) {
            abort( 403, "Number of routes exceeds maximum allowed ({$count}/" . env('MAX_ROUTES',1000) . ")" );
        }

        // reset cache used flag after above count query:
        $this->cacheUsed = false;

        return $this->verifyAndSendJSON( 'routes', $this->getTableRoutes($table), ['from_cache' => $this->cacheUsed, 'ttl_mins' => env( 'CACHE_ROUTES', 5 ) ] );
    }

    private function getLookupRoutesTable($net,$table) {
        if( $routes = Cache::get( $this->cacheKey() . 'routes-lookup-' . $net . '-table-' . $table ) ) {
            $this->cacheUsed = true;
        } else {
            $routes = app('Bird')->routesLookupTable($net,$table);
            Cache::put($this->cacheKey() . 'routes-lookup-' . $net . '-table-' . $table, $routes, env( 'CACHE_ROUTES', 5 ) );
        }
        return $routes;
    }

    public function lookupTable( $net, $table = 'master' ) {
        $net = urldecode($net);

        $this->assertValidPrefix($net);

        // let's make sure the table is valid:
        if( !in_array( $table, $this->getSymbols()['routing table'] ) ) {
            abort( 404, "Invalid table" );
        }

        // reset cache used flag after above query:
        $this->cacheUsed = false;

        return $this->verifyAndSendJSON( 'routes', $this->getLookupRoutesTable($net, $table), ['from_cache' => $this->cacheUsed, 'ttl_mins' => env( 'CACHE_ROUTES', 5 ) ] );
    }

    private function getLookupRoutesProtocol($net,$protocol) {
        if( $routes = Cache::get( $this->cacheKey() . 'routes-lookup-' . $net . '-protocol-' . $protocol ) ) {
            $this->cacheUsed = true;
        } else {
            $routes = app('Bird')->routesLookupProtocol($net,$protocol);
            Cache::put($this->cacheKey() . 'routes-lookup-' . $net . '-protocol-' . $protocol, $routes, env( 'CACHE_ROUTES', 5 ) );
        }
        return $routes;
    }

    public function lookupProtocol( $net, $protocol ) {
        $net = urldecode($net);

        $this->assertValidPrefix($net);

        // let's make sure the protocol is valid:
        if( !in_array( $protocol, $this->getSymbols()['protocol'] ) ) {
            abort( 404, "Invalid protocol" );
        }

        // reset cache used flag after above query:
        $this->cacheUsed = false;

        return $this->verifyAndSendJSON( 'routes', $this->getLookupRoutesProtocol($net, $protocol), ['from_cache' => $this->cacheUsed, 'ttl_mins' => env( 'CACHE_ROUTES', 5 ) ] );
    }

    private function getLookupRoutesExport($net,$protocol) {
        if( $routes = Cache::get( $this->cacheKey() . 'routes-lookup-' . $net . '-export-' . $protocol ) ) {
            $this->cacheUsed = true;
        } else {
            $routes = app('Bird')->routesLookupExport($net,$protocol);
            Cache::put($this->cacheKey() . 'routes-lookup-' . $net . '-export-' . $protocol, $routes, env( 'CACHE_ROUTES', 5 ) );
        }
        return $routes;
    }

    public function lookupExport( $net, $protocol ) {
        $net = urldecode($net);

        $this->assertValidPrefix($net);

        // let's make sure the protocol is valid:
        if( !in_array( $protocol, $this->getSymbols()['protocol'] ) ) {
            abort( 404, "Invalid protocol" );
        }

        // reset cache used flag after above query:
        $this->cacheUsed = false;

        return $this->verifyAndSendJSON( 'routes', $this->getLookupRoutesExport($net, $protocol), ['from_cache' => $this->cacheUsed, 'ttl_mins' => env( 'CACHE_ROUTES', 5 ) ] );
    }

}
