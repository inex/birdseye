<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    protected function verifyAndSendJSON( $response, $api = null ) {
        if( $api === null ) {
            $api = [];
        }

        $api['version'] = env('API_VERSION','0.0.0');

        if( !is_array($response) ) {
            abort(503, "Unknown internal error");
        }

        return response()->json(['api' => $api, 'status' => $response]);

    }
}
