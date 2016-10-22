<?php

namespace App\Bird\Parser;

use App\Bird\Parser;
use DateTime;

class Status extends Parser
{
    public function __contrust( $data ) {
        parent::__contrust($data);
        return $this;
    }

    public function parse() {
        $response = [];
        $last_line = "";
        $matches = "";

        foreach( preg_split("/((\r?\n)|(\r\n?))/", $this->data()) as $line ) {

            if( preg_match( "/^BIRD\s([0-9\.]+)\s*$/", $line, $matches ) ) {
                $response['version'] = $matches[1];
            }
            else if( preg_match( "/^Router\sID\sis\s([0-9\.]+)\s*$/", $line, $matches ) ) {
                $response['router_id'] = $matches[1];
            }
            else if( preg_match( "/^Current\sserver\stime\sis\s([0-9\-]+)\s([0-9\:]+)\s*$/", $line, $matches ) ) {
                $response['server_time' ] = DateTime::createFromFormat( 'Y-m-d H:i:s', "{$matches[1]} {$matches[2]}" );
            }
            else if( preg_match( "/^Last\sreboot\son\s([0-9\-]+)\s([0-9\:]+)\s*$/", $line, $matches ) ) {
                $response['last_reboot' ] = DateTime::createFromFormat( 'Y-m-d H:i:s', "{$matches[1]} {$matches[2]}" );
            }
            else if( preg_match( "/^Last\sreconfiguration\son\s([0-9\-]+)\s([0-9\:]+)\s*$/", $line, $matches ) ) {
                $response['last_reconfig' ] = DateTime::createFromFormat( 'Y-m-d H:i:s', "{$matches[1]} {$matches[2]}" );
            }

            if( !preg_match( "/^\s*$/", $line ) ) {
                $last_line = $line;
            }
        }

        $response['message'] = $last_line;

        return $response;
    }
}
