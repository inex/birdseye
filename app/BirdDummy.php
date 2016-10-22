<?php

namespace App;

use App\Bird\Parser\Status as StatusParser;

class BirdDummy
{

    public function __construct() {
    }

    public function status() {
        $status = file_get_contents( realpath(__DIR__.'/../data/sample-bird/v4-show-status') );

        return ( new StatusParser($status) )->parse();
    }



}
