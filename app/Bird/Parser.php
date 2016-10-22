<?php

namespace App\Bird;

abstract class Parser {

    private $data = null;

    public function __construct( $data ) {
        $this->data = $data;
    }

    public function data() {
        return $this->data;
    }

}
