<?php

namespace App\Http\Controllers\LookingGlass\Protocols;

use Laravel\Lumen\Routing\Controller as BaseController;

class Bgp extends BaseController
{
    public function summary() {
        // get bgp protocol data
        return app()->make('view')->make('lg/protocols/bgp/summary')->with( [
            'bgpSummary' => json_decode( app()->call('\App\Http\Controllers\Protocols@bgp')->content() )
        ] );
    }
}
