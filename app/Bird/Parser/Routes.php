<?php

namespace App\Bird\Parser;

use App\Bird\Parser;
use DateTime;

class Routes extends Parser
{
    public function __contrust( $data ) {
        parent::__contrust($data);
        return $this;
    }

    public function parse() {

        $routes  = [];
        $r       = [];
        $matches = [];

        foreach( preg_split("/((\r?\n)|(\r\n?))/",  $this->data() ) as $line ) {

            // first line is BIRD version
            if( substr( $line, 0, 4 ) == 'BIRD' ) {
                continue;
            }

            if( substr( $line, 0, 17 ) == 'Access restricted' ) {
                continue;
            }

            // should always be starting with a route definition
            if( $r == [] && preg_match( "/^\s+.*$/", $line ) ) {
                continue;
            }

            if( preg_match( "/^([0-9a-f\.\:\/]+)\s+via\s+([0-9a-f\.\:]+)\s+on\s+(\w+)\s+\[(\w+)\s+[0-9\-\:]+(\s+from\s+[0-9a-f\.\:\/]+){0,1}\]\s+(\*\s+){0,1}\((\d+)(:?\/\d+){0,1}\).*$/", $line, $matches ) ) {
                // 188.93.0.0/21      via 193.242.111.54 on eth1   [pb_0127_as42227 2016-10-09] * (100) [AS42227i]
                // 2a02:2078::/32 via 2001:7f8:18:210::15 on ens160 [pb_as43760_vli226_ipv6 2016-10-13 from 2001:7f8:18:210::8] (100) [AS47720i]
                // 94.247.48.52/30    via 93.92.8.65 on eth1 [pb_core_rl01 2016-10-19 from 93.92.8.20] * (100/65559) [?]

                // this is the start of a route definition - so store the previous one if it exists:
                if( $r !== [] ) {
                    $routes[] = $r;
                    $r = [];
                }

                $r['network']         = $matches[1];
                $r['gateway']         = $matches[2];
                $r['interface']       = $matches[3];
                $r['from_protocol']   = $matches[4];
                $r['metric']          = intval( $matches[7] );
            }
            else if( preg_match( "/^\s+Type:\s+(.*)\s*$/", $line, $matches ) ) {
                // 	Type: BGP unicast univ
                $r['type'] = explode(' ', trim($matches[1]) );
            }
            else if( preg_match( "/^\s+BGP.origin:\s+(\w+)\s*$/", $line, $matches ) ) {
                // 	BGP.origin: IGP
                $r['bgp']['origin'] = $matches[1];
            }
            else if( preg_match( "/^\s+BGP.as_path:\s+(.*)\s*$/", $line, $matches ) ) {
                // 	BGP.as_path: 42227
                $r['bgp']['as_path'] = explode(' ', trim($matches[1]) );
            }
            else if( preg_match( "/^\s+BGP.next_hop:\s+([0-9a-f\.\:\/]+)\s*$/", $line, $matches ) ) {
                // 	BGP.next_hop: 193.242.111.54
                $r['bgp']['next_hop'] = $matches[1];
            }
            else if( preg_match( "/^\s+BGP.local_pref:\s+(\w+)\s*$/", $line, $matches ) ) {
                // 	BGP.origin: IGP
                $r['bgp']['local_pref'] = $matches[1];
            }
            else if( preg_match( "/^\s+BGP.med:\s+(\d+)\s*$/", $line, $matches ) ) {
                // 	BGP.med: 100
                $r['bgp']['med'] = intval( $matches[1] );
            }
            else if( preg_match( "/^\s+BGP.community:\s+(.+)\s*$/", $line, $matches ) ) {
                // 	BGP.community: (0,31122) (0,6543)
                foreach( explode( ' ', trim( $matches[1] ) ) as $community ) {
                    if( preg_match( "/^\((\d+),(\d+)\)/", $community, $matches ) ) {
                        $r['bgp']['communities'][] = [ intval( $matches[1] ), intval( $matches[2] ) ];
                    }
                }
            }
        }

        // catche the last one:
        if( $r !== [] ) {
            $routes[] = $r;
        }

        return $routes;
    }
}
