<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

class Controller extends BaseController
{
    private $cacheKey;

    public function __construct() {
        if( !env('CACHE_KEY',false) ) {  //}|| !is_executable( env('BIRDC') ) ) {
            abort( 500, "Cache key not specified" );
        }
        $this->cacheKey = env('CACHE_KEY');
    }

    public function cacheKey() {
        return $this->cacheKey;
    }

    protected function verifyAndSendJSON( $key, $response, $api = null ) {
        if( $api === null ) {
            $api = [];
        }

        $api['version'] = env('API_VERSION','0.0.0');

        if( !is_array($response) ) {
            abort(503, "Unknown internal error");
        }

        return response()->json(['api' => $api, $key => $response]);

    }
}
