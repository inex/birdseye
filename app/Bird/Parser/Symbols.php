<?php

namespace App\Bird\Parser;

use App\Bird\Parser;
use DateTime;

class Symbols extends Parser
{
    public function __contrust( $data ) {
        parent::__contrust($data);
        return $this;
    }

    public function parse() {
        $response = [];
        $matches = [];

        foreach( preg_split("/((\r?\n)|(\r\n?))/", $this->data()) as $line ) {

            if( substr( $line, 0, 4 ) == 'BIRD' ) {
                continue;
            }

            if( preg_match( "/^([^\s]+)\s+(.+)\s*$/", $line, $matches ) ) {
                $response[$matches[2]][] = $matches[1];
            }
        }

        return $response;
    }
}
