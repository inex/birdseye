<?php

namespace App\Bird\Parser;

use App\Bird\Parser;

abstract class Protocol extends Parser {

    public function __construct( $data ) {
        parent::__construct($data);
    }

}
