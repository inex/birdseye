<?php

namespace App\Bird\Parser\Routes;

use App\Bird\Parser;
use DateTime;

class Count extends Parser
{
    public function __construct( $data ) {
        parent::__construct($data);
    }

    public function parse() {

        $count = [];

        foreach( preg_split("/((\r?\n)|(\r\n?))/",  $this->data() ) as $line ) {

            // first line is BIRD version
            if( substr( $line, 0, 4 ) == 'BIRD' ) {
                continue;
            }

            if( substr( $line, 0, 17 ) == 'Access restricted' ) {
                continue;
            }

            if( preg_match( "/^(\d+)\s+of\s+(\d+)\s+routes.*$/", $line, $matches ) ) {
                // 4 of 12 routes for 12 networks
                $count['routes'] = intval( $matches[1] );
            }
        }

        return $count;
    }
}
