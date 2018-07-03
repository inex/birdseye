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

            if( preg_match( "/^([0-9a-f\.\:\/]+)\s+via\s+([0-9a-f\.\:]+)\s+on\s+([a-zA-Z0-9_\.\-\/]+)\s+\[(\w+)\s+([0-9\-\:]+(?:\s[0-9\-\:]+){0,1})(?:\s+from\s+([0-9a-f\.\:\/]+)){0,1}\]\s+(?:(\*)\s+){0,1}\((\d+)(?:\/\d+){0,1}\).*$/", $line, $matches ) ) {
                // 188.93.0.0/21      via 193.242.111.54 on eth1   [pb_0127_as42227 2016-10-09] * (100) [AS42227i]
                // 2a02:2078::/32 via 2001:7f8:18:210::15 on ens160 [pb_as43760_vli226_ipv6 2016-10-13 from 2001:7f8:18:210::8] (100) [AS47720i]
                // 94.247.48.52/30    via 93.92.8.65 on eth1 [pb_core_rl01 2016-10-19 from 93.92.8.20] * (100/65559) [?]
                // 5.159.40.0/21      via 193.242.111.74 on eth1 [pb_0136_as61194 2016-03-12] * (100) [AS61194i]
                //  203.159.70.0/24    via 203.159.68.3 on eth0.99 [pb_0065_as63528 2018-07-01] * (100) [AS63528i]

                // this is the start of a route definition - so store the previous one if it exists:
                if( $r !== [] ) {
                    $routes[] = $r;
                    $r = [];
                }
                $this->mainRouteDetail( $matches, $r );
            }
            else if( preg_match( "/^\s+via\s+([0-9a-f\.\:]+)\s+on\s+([a-zA-Z0-9_\.\-\/]+)\s+\[(\w+)\s+([0-9\-\:]+(?:\s[0-9\-\:]+){0,1})(?:\s+from\s+([0-9a-f\.\:\/]+)){0,1}\]\s+(?:(\*)\s+){0,1}\((\d+)(?:\/\d+){0,1}\).*$/", $line, $matches ) ) {
                // second entry for previous route
                if( $r == [] ) {
                    // something's not right:
                    continue;
                } else {
                    $routes[]     = $r;
                    $network      = $r['network'];
                    $r            = [];
                    
                    // rearrange $matches to have the same positions as above
                    $regMatch = array_shift($matches);
                    array_unshift( $matches, $network );
                    array_unshift( $matches, $regMatch );
                }
                $this->mainRouteDetail( $matches, $r );
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
            else if( preg_match( "/^\s+BGP.atomic_aggr:(.*)$/", $line, $matches ) ) {
                // 	BGP.atomic_aggr:
                $r['bgp']['atomic_aggr'] = trim( $matches[1] );
            }
            else if( preg_match( "/^\s+BGP.aggregator:(.*)$/", $line, $matches ) ) {
                // 	BGP.aggregator: 193.104.155.9 AS50145
                $r['bgp']['aggregator'] = trim( $matches[1] );
            }
            else if( preg_match( "/^\s+BGP.community:\s+(.+)\s*$/", $line, $matches ) ) {
                // 	BGP.community: (0,31122) (0,6543)
                foreach( explode( ' ', trim( $matches[1] ) ) as $community ) {
                    if( preg_match( "/^\((\d+),(\d+)\)/", $community, $matches ) ) {
                        $r['bgp']['communities'][] = [ intval( $matches[1] ), intval( $matches[2] ) ];
                    }
                }
            }
            else if( preg_match( "/^\s+BGP.large_community:\s+(.+)\s*$/", $line, $matches ) ) {
                // BGP.large_community: (999, 1, 111)
                // BGP.large_community: (999, 1, 111) (999, 156, 111) (999, 157, 111)
                $m = substr( trim( $matches[1] ), 1, -1 );
                foreach( explode( ') (', $m ) as $community ) {
                    if( preg_match( "/^(\d+),\s*(\d+),\s*(\d+)/", trim( $community ), $matches ) ) {
                        $r['bgp']['large_communities'][] = [ intval( $matches[1] ), intval( $matches[2] ), intval( $matches[3] )  ];
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
    
    private function mainRouteDetail( $matches, &$r ) {
        $r['network']         = $matches[1];
        $r['gateway']         = $matches[2];
        $r['interface']       = $matches[3];
        $r['from_protocol']   = $matches[4];

        if( preg_match( '/^\d\d\d\d\-\d{1,2}\-\d{1,2}\s\d{1,2}:\d{1,2}:\d{1,2}$/', $matches[5] ) ) {
            $r['age'] = DateTime::createFromFormat( 'Y-m-d H:i:s', $matches[5] )->format('c');
        } else if( preg_match( '/^\d{1,2}:\d{1,2}:\d{1,2}$/', $matches[5] ) ) {
            $r['age'] = DateTime::createFromFormat( 'Y-m-d H:i:s', date('Y-m-d') . ' ' . $matches[5] )->format('c');
        } else if( preg_match( '/^\d\d\d\d\-\d{1,2}\-\d{1,2}$/', $matches[5] ) ) {
            $r['age'] = DateTime::createFromFormat( 'Y-m-d H:i:s', $matches[5] . ' 00:00:00' )->format('c');
        } else {
            $r['age'] = '0000-00-00T00:00:00+00:00';
        }

        $r['learnt_from']     = $matches[6];
        $r['primary']         = $matches[7] == '*' ? true : false;
        $r['metric']          = intval( $matches[8] );
        return $r;
    }
}
