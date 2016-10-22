<?php

namespace App\Http\Controllers;

class Routes extends Controller
{
    public function protocol($protocol)
    {
        $routes = app('Bird')->routesProtocol($protocol);

        return $this->verifyAndSendJSON( 'routes', $routes );
    }

}
