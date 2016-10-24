<?php

namespace App;

use App\Bird\Parser\Routes as RoutesParser;
use App\Bird\Parser\Routes\Count as RoutesCountParser;
use App\Bird\Parser\Status as StatusParser;
use App\Bird\Parser\Symbols as SymbolsParser;
use App\Bird\Parser\Protocol\Bgp as BgpProtocolParser;

class Bird
{
    private $cmd = null;

    public function __construct( $cmd ) {
        $this->cmd = $cmd;
    }

    public function cmd() {
        return $this->cmd;
    }

    private function run( $show ) {

        $output = shell_exec( $this->cmd() . " " . escapeshellarg($show) );

        if( $output === null ) {
            abort( 503, "Error querying bird" );
        }

        if( !preg_match( "/^BIRD\s+[0-9\.]+\s+ready/", $output ) ) {
            abort( 503, "Error querying bird" );
        }

        return $output;
    }

    private function cachedOrRun( $key ) {
        Cache::remember( $this->cacheKey() . $key, 1, function() use ( $key ) {
            return $this->run( $key );
        });
    }

    public function status() {
        return ( new StatusParser($this->run('show status')) )->parse();
    }

    public function symbols() {
        return ( new SymbolsParser($this->run('show symbols')) )->parse();
    }

    public function protocols() {
        $protocolsBlob = $this->run('show protocols all');

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
        $routesBlob = $this->run('show route protocol ' . $protocol . ' all');

        return ( new RoutesParser($routesBlob ) )->parse();
    }

    public function routesProtocolCount( $protocol ) {
        $routesCountBlob = $this->run('show route protocol ' . $protocol . ' count');

        return ( new RoutesCountParser( $routesCountBlob ) )->parse();
    }


    public function routesTable( $table ) {
        $routesBlob = $this->run('show route table ' . $table . ' all');

        return ( new RoutesParser($routesBlob ) )->parse();
    }

    public function routesTableCount( $table ) {
        $routesCountBlob = $this->run('show route table ' . $table . ' count');

        return ( new RoutesCountParser( $routesCountBlob ) )->parse();
    }

    public function routesLookupTable( $net, $table ) {
        $routesBlob = $this->run('show route for ' . $net . ' table ' . $table . ' all');

        return ( new RoutesParser($routesBlob ) )->parse();
    }

    public function routesLookupProtocol( $net, $protocol ) {
        $routesBlob = $this->run('show route for ' . $net . ' protocol ' . $protocol . ' all');

        return ( new RoutesParser($routesBlob ) )->parse();
    }
}
