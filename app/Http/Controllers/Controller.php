<?php

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;

use Cache;

class Controller extends BaseController
{
    private   $cacheKey;
    protected $cacheUsed      = false;
    protected $cacheDisabled  = false;
    protected $ipWhitelisted  = false;
    protected $skipCache      = false;


    public function __construct() {
        $this->cacheKey      = $_ENV['BIRDSEYE_CACHE_KEY'];
        $this->ipWhitelisted = $this->ipWhitelisted();
        $this->skipCache     = $this->skipCache();
        $this->cacheDisabled = ( env( 'CACHE_DRIVER' ) == 'array' ) || $this->skipCache;
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
            $api['env']            = $_ENV['BIRDSEYE_ENV_FILE'];
            $api['cache_disabled'] = $this->cacheDisabled;
            $api['ip_whitelisted'] = $this->skipCache();
        }
        $api['max_routes'] = intval(env('MAX_ROUTES',1000));

        if( !is_array($response) ) {
            abort(503, "Unknown internal error");
        }

        // check cache status
        if( env( 'CACHE_DRIVER' ) == 'array' ) {
            $api['from_cache'] = false;
            if( isset( $api['ttl_mins'] ) ) {
                unset( $api['ttl_mins'] );
            }
        }

        return response()->json(['api' => $api, $key => $response]);
    }

    protected function getSymbols() {
        if( !$this->cacheDisabled && $symbols = Cache::get( $this->cacheKey() . 'symbols' ) ) {
            $this->cacheUsed = true;
        } else {
            $symbols = app('Bird')->symbols();
            Cache::put($this->cacheKey() . 'symbols', $symbols, env( 'CACHE_SHOW_SYMBOLS', 5 ) );
        }
        return $symbols;
    }

    /**
     * For a set of whitelisted IP addresses, we'll allow cache skipping
     */
    protected function ipWhitelisted() {
        if( file_exists( __DIR__ . '/../../../skipcache_ips.php' ) ) {
            $ips = include __DIR__ . '/../../../skipcache_ips.php';

            if( isset( $ips ) && is_array( $ips ) ) {
                return( in_array( $_SERVER['REMOTE_ADDR'], $ips ) );
            }
        }

        return false;
    }


    /**
     * Should the cache be skipped?
     */
    protected function skipCache() {
        if( $this->cacheDisabled ) {
            return true;
        }

        if( $this->ipWhitelisted() ) {
            if( isset( $_GET['use_cache'] ) && $_GET['use_cache'] == "0" ) {
                return true;
            }
        }

        return false;
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
