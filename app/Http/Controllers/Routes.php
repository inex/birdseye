<?php

namespace App\Http\Controllers;

class Routes extends Controller
{
    public function protocol($protocol)
    {
        // let's make sure the protocol is valid:
        if( !in_array( $protocol, app('Bird')->symbols()['protocol'] ) ) {
            abort( 404, "Invalid protocol" );
        }

        $routes = app('Bird')->routesProtocol($protocol);

        return $this->verifyAndSendJSON( 'routes', $routes );
    }

    public function table($table)
    {
        // let's make sure the protocol is valid:
        if( !in_array( $table, app('Bird')->symbols()['routing table'] ) ) {
            abort( 404, "Invalid table" );
        }

        $routes = app('Bird')->routesTable($table);

        return $this->verifyAndSendJSON( 'routes', $routes );
    }

}
