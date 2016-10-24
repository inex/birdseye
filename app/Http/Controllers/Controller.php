<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

use Cache;

class Controller extends BaseController
{
    private $cacheKey;
    protected $cacheUsed = false;


    public function __construct() {
        if( !isset( $_ENV['BIRDSEYE_CACHE_KEY'] ) ) {
            abort( 500, "Cache key not specified" );
        }
        $this->cacheKey = $_ENV['BIRDSEYE_CACHE_KEY'];
    }

    public function cacheKey() {
        return $this->cacheKey;
    }

    protected function verifyAndSendJSON( $key, $response, $api = null ) {
        if( $api === null ) {
            $api = [];
        }

        $api['version'] = $_ENV['BIRDSEYE_API_VERSION'];
        if( env('APP_DEBUG',false)) {
            $api['env']     = $_ENV['BIRDSEYE_ENV_FILE'];
        }

        if( !is_array($response) ) {
            abort(503, "Unknown internal error");
        }

        return response()->json(['api' => $api, $key => $response]);
    }

    protected function getSymbols() {
        if( $symbols = Cache::get( $this->cacheKey() . 'symbols' ) ) {
            $this->cacheUsed = true;
        } else {
            $symbols = app('Bird')->symbols();
            Cache::put($this->cacheKey() . 'symbols', $symbols, env( 'CACHE_SHOW_SYMBOLS', 1 ) );
        }
        return $symbols;
    }

    protected function assertValidPrefix($net) {
        // validate net as a IP / network
        if( !( preg_match( "/^([0-9]{1,3}\.){3}[0-9]{1,3}(\/\d{1,2}){0,1}$/", $net)
                || preg_match( "/^[a-f0-9\:]+(\/\d{1,3}){0,1}$/", $net ) ) ) {

            // FIXME: a better v6 checker would be useful
            abort(400,'Bad IP address');
        }
    }

}
