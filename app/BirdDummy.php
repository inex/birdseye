<?php

namespace App;

use App\Bird\Parser\Routes as RoutesParser;
use App\Bird\Parser\Status as StatusParser;
use App\Bird\Parser\Symbols as SymbolsParser;
use App\Bird\Parser\Protocol\Bgp as BgpProtocolParser;

class BirdDummy
{

    public function __construct() {
    }

    public function status() {
        $status = file_get_contents( realpath(__DIR__.'/../data/sample-bird/v4-show-status') );

        return ( new StatusParser($status) )->parse();
    }

    public function symbols() {
        $symbols = file_get_contents( realpath(__DIR__.'/../data/sample-bird/symbols') );
        return ( new SymbolsParser($symbols) )->parse();
    }

    public function protocols() {
        $protocolsBlob = file_get_contents( realpath(__DIR__.'/../data/sample-bird/v4-show-protocols-all') );

        // each protocol is separated by a blank line
        $protocolBlobs = [];

        $protocol = "";
        foreach( preg_split("/((\r?\n)|(\r\n?))/", $protocolsBlob ) as $line ) {

            if( preg_match( "/^\s*$/", $line ) ) {
                // blank line, if we have data, save it
                if( strlen( trim( $protocol) ) > 0 ) {
                    $protocolBlobs[] = $protocol;
                }

                $protocol = "";
                continue;
            }

            $protocol .= "{$line}\n";

        }

        return $protocolBlobs;
    }

    public function protocolsBgp()
    {
        $protocolBlobs = $this->protocols();

        // we not have an array with the string blob of each protocol
        // let's remove non BGP and reindex by protocol name
        $bgpProtocolsText = [];
        $matches = [];
        foreach( $protocolBlobs as $i => $blob ) {
            if( !preg_match( "/^(\w+)\s+BGP\s+.*/", $blob, $matches ) ) {
                unset( $protocolBlobs[$i] );
                continue;
            }

            $bgpProtocolsText[$matches[1]] = $protocolBlobs[$i];
        }

        $protocols = [];

        foreach( $bgpProtocolsText as $name => $data ) {
            $protocols[$name] = ( new BgpProtocolParser($data) )->parse();
        }

        return $protocols;
    }

    public function routesProtocol( $protocol ) {
        $routesBlob = file_get_contents( realpath(__DIR__.'/../data/sample-bird/v4-show-route-protocol') );

        return ( new RoutesParser($routesBlob ) )->parse();
    }

    public function routesTable( $table ) {
        $routesBlob = file_get_contents( realpath(__DIR__.'/../data/sample-bird/v4-show-route-table-all') );

        return ( new RoutesParser($routesBlob ) )->parse();
    }

}
