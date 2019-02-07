<?php

namespace App\Bird\Parser\Protocol;

use App\Bird\Parser\Protocol as ProtocolParser;

use DateTime;

class Bgp extends ProtocolParser
{
    public function __construct( $data ) {
        parent::__construct($data);
    }

    public function parse() {
        $p = [];
        $last_line = "";
        $matches = "";

        foreach( preg_split("/((\r?\n)|(\r\n?))/", $this->data()) as $line ) {

            if( preg_match( "/^(\w+)\s+BGP\s+([\-\w]+)\s+(\w+)\s+([0-9\-]+\s[0-9\:]+)(\s*|\s+(\w+).*)$/", $line, $matches ) ) {
                // pb_0109_as42    BGP      t_0109_as42       up     2016-09-30 14:18:49  Established
                // pb_0081_as30900 BGP      t_0081_as30900    start  2015-11-27 14:18:49  Active        Socket: No route to host
                // R244x1     BGP        ---        up     2019-01-23 14:18:49  Established
                $p['protocol']      = $matches[1];
                $p['bird_protocol'] = 'BGP';
                $p['table']         = $matches[2];
                $p['state']         = $matches[3];
                $p['state_changed'] = DateTime::createFromFormat( 'Y-m-d H:i:s', $matches[4] )->format('c');
                $p['connection']    = trim( $matches[5] ) ? $matches[5] : '';
            }
            else if( preg_match( "/^\s+Description:\s+(.*)\s*$/", $line, $matches ) ) {
                //   Description:    RIB for AS42 - Packet Clearing House DNS - VLAN Interface 109
                $p['description'] = $matches[1];
                
                if( env( 'PARSER_PROTOCOL_BGP_DESCRIPTION', false ) && strlen( env( 'PARSER_PROTOCOL_BGP_DESCRIPTION' ) ) && @preg_match('/^\s+Description:\s+'.env( 'PARSER_PROTOCOL_BGP_DESCRIPTION' ).'$/', null) !== false ) {
                    if( preg_match( '/^\s+Description:\s+'.env( 'PARSER_PROTOCOL_BGP_DESCRIPTION' ).'$/', $line, $matches ) ) { //}&& isset( $matches[1] ) ) {
                        $p['description_short'] = $matches[1];
                    }
                }
            }
            else if( preg_match( "/^\s+Table:\s+(.*+)\s*$/", $line, $matches ) ) {
                //   Table:          t_R244x1
                $p['table'] = $matches[1];
            }
            else if( preg_match( "/^\s+Preference:\s+([0-9]+)\s*$/", $line, $matches ) ) {
                //   Preference:     100
                $p['preference'] = intval( $matches[1] );
            }
            else if( preg_match( "/^\s+Input filter:\s+([^\s]+)\s*$/", $line, $matches ) ) {
                //   Input filter:   (unnamed)
                $p['input_filter'] = $matches[1];
            }
            else if( preg_match( "/^\s+Output filter:\s+([^\s]+)\s*$/", $line, $matches ) ) {
                //   Output filter:   ACCEPT
                $p['output_filter'] = $matches[1];
            }
            else if( preg_match( "/^\s+Import limit:\s+([\d]+)\s*$/", $line, $matches ) ) {
                //   Import limit:   1000
                $p['import_limit'] = intval( $matches[1] );
            }
            else if( preg_match( "/^\s+Action:\s+([\w]+)\s*$/", $line, $matches ) ) {
                //     Action:       restart
                $p['limit_action'] = $matches[1];
            }
            else if( preg_match( "/^\s+Routes:\s+(\d+)\s+imported,\s+(\d+)\s+exported\s*$/", $line, $matches ) ) {
                //   Routes:         35 imported, 41127 exported, 2590 preferred
                $p['routes'] = [
                    'imported'  => intval( $matches[1] ),
                    'exported'  => intval( $matches[2] ),
                    'preferred' => 0,
                ];
            }
            else if( preg_match( "/^\s+Import updates:\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)\s*$/", $line, $matches ) ) {
                //     Import updates:             94          0          0          0         94
                $p['route_changes']['import_updates'] = [
                    'received'  => intval( $matches[1] ),
                    'rejected'  => intval( $matches[2] ),
                    'filtered'  => intval( $matches[3] ),
                    'ignored'   => intval( $matches[4] ),
                    'accepted'  => intval( $matches[5] ),
                ];
            }
            else if( preg_match( "/^\s+Import withdraws:\s+(\d+)\s+(\d+)\s+\-\-\-\s+(\d+)\s+(\d+)\s*$/", $line, $matches ) ) {
                //     Import withdraws:           59          0        ---          0         59
                $p['route_changes']['import_withdraws'] = [
                    'received'  => intval( $matches[1] ),
                    'rejected'  => intval( $matches[2] ),
                    'ignored'   => intval( $matches[3] ),
                    'accepted'  => intval( $matches[4] ),
                ];
            }
            else if( preg_match( "/^\s+Export updates:\s+(\d+)\s+(\d+)\s+(\d+)\s+\-\-\-\s+(\d+)\s*$/", $line, $matches ) ) {
                //     Export updates:        1089442         94          0        ---    1089348
                $p['route_changes']['export_updates'] = [
                    'received'  => intval( $matches[1] ),
                    'rejected'  => intval( $matches[2] ),
                    'filtered'  => intval( $matches[3] ),
                    'accepted'  => intval( $matches[4] ),
                ];
            }
            else if( preg_match( "/^\s+Export withdraws:\s+(\d+)(\s+\-\-\-)+\s+(\d+)\s*$/", $line, $matches ) ) {
                //     Export withdraws:       216076        ---        ---        ---     216017
                $p['route_changes']['export_withdraws'] = [
                    'received'  => intval( $matches[1] ),
                    'accepted'  => intval( $matches[3] ),
                ];
            }
            else if( preg_match( "/^\s+BGP state:\s+(\w+)\s*$/", $line, $matches ) ) {
                //   BGP state:          Established
                $p['bgp_state'] = $matches[1];
            }
            else if( preg_match( "/^\s+Neighbor address:\s+([^\s]+)\s*$/", $line, $matches ) ) {
                //     Neighbor address: 193.242.111.60
                $p['neighbor_address'] = $matches[1];
            }
            else if( preg_match( "/^\s+Neighbor AS:\s+([\d]+)\s*$/", $line, $matches ) ) {
                //     Neighbor AS:      42
                $p['neighbor_as'] = intval( $matches[1] );
            }
            else if( preg_match( "/^\s+Neighbor ID:\s+([^\s]+)\s*$/", $line, $matches ) ) {
                //     Neighbor address: 193.242.111.60
                $p['neighbor_id'] = $matches[1];
            }
            else if( preg_match( "/^\s+Neighbor caps:\s+(.*)$/", $line, $matches ) ) {
                //     Neighbor caps:    refresh
                $p['neighbor_capabilities'] = explode( ' ', trim( $matches[1] ) );
            }
            else if( preg_match( "/^\s+Session:\s+(.*)$/", $line, $matches ) ) {
                //     Session:          external route-server
                $p['bgp_session'] = explode( ' ', trim( $matches[1] ) );
            }
            else if( preg_match( "/^\s+Source address:\s+([^\s]+)\s*$/", $line, $matches ) ) {
                //     Source address:   193.242.111.8
                $p['source_address'] = $matches[1];
            }
            else if( preg_match( "/^\s+Route limit:\s+(\d+)\/(\d+)\s*$/", $line, $matches ) ) {
                //     Route limit:      35/1000
                $p['route_limit_at'] = intval( $matches[1] );
            }
            else if( preg_match( "/^\s+Hold timer:\s+([\d\.]+)\/([\d\.]+)\s*$/", $line, $matches ) ) {
                //     Hold timer:       124.5/180
                $p['hold_timer'] = intval( $matches[2] );
            }
            else if( preg_match( "/^\s+Keepalive timer:\s+([\d\.]+)\/([\d\.]+)\s*$/", $line, $matches ) ) {
                //     Keepalive timer:  39.5/60
                $p['keepalive'] = intval( $matches[2] );
            }

        }

        // Bird 2
        if( !isset( $p['route_limit_at'] ) && isset( $p['routes']['imported'] ) ) {
            $p['route_limit_at'] = $p['routes']['imported'];
        }

        return $p;
    }
}
