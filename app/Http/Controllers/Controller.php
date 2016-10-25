<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

use Cache;

class Controller extends BaseController
{
    private $cacheKey;
    protected $cacheUsed = false;


    public function __construct() {
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
        $api['max_routes'] = intval(env('MAX_ROUTES',1000));

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
        if( !strpos($net,':') ) {
            if( preg_match( "/^(?:[0-9]{1,3}\.){3}[0-9]{1,3}(:?\/\d{1,2}){0,1}$/", $net ) ) {
                return true;
            }
        } else {
            // from Zend/Validate/Ip.php (BSD)

            // pull the mask if it's set
            if( strpos($net, '/') ) {
                $mask = substr( $net, strpos($net, '/')+1 );
                if( !( preg_match( "/^[0-9]{1,3}$/", $mask ) && $mask >= 0 && $mask <= 128 ) ) {
                    abort(400,'Bad IP address');
                }

                $net = substr($net, 0, strpos($net, '/') );
            }

            if( strpos($net, '.') ) {
                $lastcolon = strrpos($net, ':');
                if (!($lastcolon && preg_match( "/^(?:[0-9]{1,3}\.){3}[0-9]{1,3}(:?\/\d{1,2}){0,1}$/", substr($net, $lastcolon + 1 ) ) ) ) {
                    abort(400,'Bad IP address');
                }

                $net = substr($net, 0, $lastcolon) . ':0:0';
            }

            if (strpos($net, '::') === false && preg_match('/\A(?:[a-f0-9]{1,4}:){7}[a-f0-9]{1,4}\z/i', $net) ) {
                return true;
            }

            $colonCount = substr_count($net, ':');
            if ($colonCount < 8 && preg_match('/\A(?::|(?:[a-f0-9]{1,4}:)+):(?:(?:[a-f0-9]{1,4}:)*[a-f0-9]{1,4})?\z/i', $net) ) {
                return true;
            }

            // special case with ending or starting double colon
            if( $colonCount == 8 && preg_match('/\A(?:::)?(?:[a-f0-9]{1,4}:){6}[a-f0-9]{1,4}(?:::)?\z/i', $net) ) {
                return true;
            }
        }

        abort(400,'Bad IP address');
    }

}
