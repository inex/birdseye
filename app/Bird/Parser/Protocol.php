<?php

namespace App\Bird\Parser;

use App\Bird\Parser;

abstract class Protocol extends Parser {

    public function __contrust( $data ) {
        parent::__contrust($data);
        return $this;
    }

}
